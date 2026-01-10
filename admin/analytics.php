<?php
header('Content-Type: text/html; charset=UTF-8');
mb_internal_encoding('UTF-8');
require_once '../config/db.php';
require_once 'auth.php';

Auth::require();

$pdo = getConnection();
$pdo->exec("SET NAMES utf8mb4");

// Período selecionado (padrão: 30 dias)
$periodo = isset($_GET['periodo']) ? (int)$_GET['periodo'] : 30;

// Data inicial
$dataInicial = date('Y-m-d H:i:s', strtotime("-$periodo days"));

// Métricas principais
$stmt = $pdo->prepare("SELECT COUNT(*) as total_pageviews FROM analytics_pageviews WHERE created_at >= ?");
$stmt->execute([$dataInicial]);
$totalPageviews = $stmt->fetch()['total_pageviews'];

$stmt = $pdo->prepare("SELECT COUNT(DISTINCT session_id) as visitantes_unicos FROM analytics_pageviews WHERE created_at >= ?");
$stmt->execute([$dataInicial]);
$visitantesUnicos = $stmt->fetch()['visitantes_unicos'];

// Média de páginas por visita
$mediaPaginas = $visitantesUnicos > 0 ? round($totalPageviews / $visitantesUnicos, 2) : 0;

// Top 10 páginas mais visitadas
$stmt = $pdo->prepare("
    SELECT url, titulo, COUNT(*) as visitas 
    FROM analytics_pageviews 
    WHERE created_at >= ? 
    GROUP BY url, titulo 
    ORDER BY visitas DESC 
    LIMIT 10
");
$stmt->execute([$dataInicial]);
$topPaginas = $stmt->fetchAll();

// Fontes de tráfego
$stmt = $pdo->prepare("
    SELECT 
        CASE 
            WHEN referrer = '' OR referrer IS NULL OR referrer = 'Direto' THEN 'Direto'
            WHEN referrer LIKE '%google%' THEN 'Google'
            WHEN referrer LIKE '%facebook%' THEN 'Facebook'
            WHEN referrer LIKE '%instagram%' THEN 'Instagram'
            WHEN referrer LIKE '%twitter%' OR referrer LIKE '%x.com%' THEN 'Twitter/X'
            WHEN referrer LIKE '%youtube%' THEN 'YouTube'
            WHEN referrer LIKE '%linkedin%' THEN 'LinkedIn'
            ELSE 'Outros'
        END as fonte,
        COUNT(*) as visitas
    FROM analytics_pageviews 
    WHERE created_at >= ?
    GROUP BY fonte
    ORDER BY visitas DESC
");
$stmt->execute([$dataInicial]);
$fontesTrafego = $stmt->fetchAll();

// Dados para gráfico temporal (visitas por dia)
$stmt = $pdo->prepare("
    SELECT 
        DATE(created_at) as data,
        COUNT(*) as pageviews,
        COUNT(DISTINCT session_id) as visitantes
    FROM analytics_pageviews 
    WHERE created_at >= ?
    GROUP BY DATE(created_at)
    ORDER BY data ASC
");
$stmt->execute([$dataInicial]);
$dadosTemporais = $stmt->fetchAll();

// Preparar dados para Chart.js
$datas = [];
$pageviewsPorDia = [];
$visitantesPorDia = [];

foreach ($dadosTemporais as $dado) {
    $datas[] = date('d/m', strtotime($dado['data']));
    $pageviewsPorDia[] = $dado['pageviews'];
    $visitantesPorDia[] = $dado['visitantes'];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - Painel Admin</title>
    <link rel="icon" type="image/x-icon" href="/apafut/assets/logo.ico">
    <link rel="shortcut icon" type="image/x-icon" href="/apafut/assets/logo.ico">
    <link rel="apple-touch-icon" href="/apafut/assets/logo.png">
    <link rel="icon" href="../assets/logo.ico" type="image/x-icon">
    <link rel="shortcut icon" href="../assets/logo.ico" type="image/x-icon">
    
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/noticias.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .analytics-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .period-selector {
            display: flex;
            gap: 10px;
        }
        .period-btn {
            padding: 8px 16px;
            border: 1px solid #ddd;
            background: white;
            color: #333;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
        }
        .period-btn:hover {
            background: #f8f9fa;
        }
        .period-btn.active {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .metric-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .metric-card h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #666;
            font-weight: 500;
        }
        .metric-value {
            font-size: 32px;
            font-weight: bold;
            color: #333;
            margin: 0;
        }
        .metric-icon {
            float: right;
            font-size: 24px;
            color: #007bff;
            opacity: 0.3;
        }
        .chart-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .chart-container h2 {
            margin: 0 0 20px 0;
            font-size: 18px;
            color: #333;
        }
        .analytics-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }
        .content-area {
            padding-left: 30px;
        }
        .table-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .table-container h2 {
            margin: 0 0 20px 0;
            font-size: 18px;
            color: #333;
        }
        .analytics-table {
            width: 100%;
            border-collapse: collapse;
        }
        .analytics-table th {
            text-align: left;
            padding: 12px;
            background: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            color: #333;
        }
        .analytics-table td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
        }
        .analytics-table tr:hover {
            background: #f8f9fa;
        }
        .badge-count {
            background: #007bff;
            color: white;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
        }
        .source-icon {
            width: 30px;
            text-align: center;
            margin-right: 8px;
        }
        @media (max-width: 768px) {
            .analytics-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <?php include 'includes/topbar.php'; ?>
            
            <div class="content-area">
                <div class="analytics-header">
                    <div class="page-header">
                        <h1><i class="fas fa-chart-line"></i> <span>Analytics</span></h1>
                    </div>
                    
                    <div class="period-selector">
                        <a href="?periodo=7" class="period-btn <?= $periodo == 7 ? 'active' : '' ?>">7 dias</a>
                        <a href="?periodo=30" class="period-btn <?= $periodo == 30 ? 'active' : '' ?>">30 dias</a>
                        <a href="?periodo=90" class="period-btn <?= $periodo == 90 ? 'active' : '' ?>">90 dias</a>
                    </div>
                </div>

                <div class="metrics-grid">
                    <div class="metric-card">
                        <i class="fas fa-eye metric-icon"></i>
                        <h3>Total de Visualizações</h3>
                        <p class="metric-value"><?= number_format($totalPageviews, 0, ',', '.') ?></p>
                    </div>
                    
                    <div class="metric-card">
                        <i class="fas fa-users metric-icon"></i>
                        <h3>Visitantes Únicos</h3>
                        <p class="metric-value"><?= number_format($visitantesUnicos, 0, ',', '.') ?></p>
                    </div>
                    
                    <div class="metric-card">
                        <i class="fas fa-file-alt metric-icon"></i>
                        <h3>Páginas por Visita</h3>
                        <p class="metric-value"><?= $mediaPaginas ?></p>
                    </div>
                </div>

                <div class="chart-container">
                    <h2><i class="fas fa-chart-area"></i> Evolução de Visitas</h2>
                    <canvas id="visitasChart" style="max-height: 300px;"></canvas>
                </div>

                <div class="analytics-grid">
                    <div class="table-container">
                        <h2><i class="fas fa-fire"></i> Páginas Mais Visitadas</h2>
                        <table class="analytics-table">
                            <thead>
                                <tr>
                                    <th>Página</th>
                                    <th style="text-align: right;">Visitas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($topPaginas) > 0): ?>
                                    <?php foreach ($topPaginas as $pagina): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($pagina['titulo']) ?></strong><br>
                                                <small style="color: #666;"><?= htmlspecialchars($pagina['url']) ?></small>
                                            </td>
                                            <td style="text-align: right;">
                                                <span class="badge-count"><?= number_format($pagina['visitas'], 0, ',', '.') ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="2" style="text-align: center; padding: 40px; color: #999;">
                                            <i class="fas fa-chart-bar" style="font-size: 48px; margin-bottom: 10px; display: block;"></i>
                                            Nenhum dado disponível ainda
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="table-container">
                        <h2><i class="fas fa-link"></i> Fontes de Tráfego</h2>
                        <table class="analytics-table">
                            <thead>
                                <tr>
                                    <th>Fonte</th>
                                    <th style="text-align: right;">Visitas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($fontesTrafego) > 0): ?>
                                    <?php 
                                    $icones = [
                                        'Direto' => ['class' => 'fas', 'icon' => 'fa-globe'],
                                        'Google' => ['class' => 'fab', 'icon' => 'fa-google'],
                                        'Facebook' => ['class' => 'fab', 'icon' => 'fa-facebook'],
                                        'Instagram' => ['class' => 'fab', 'icon' => 'fa-instagram'],
                                        'Twitter/X' => ['class' => 'fab', 'icon' => 'fa-twitter'],
                                        'YouTube' => ['class' => 'fab', 'icon' => 'fa-youtube'],
                                        'LinkedIn' => ['class' => 'fab', 'icon' => 'fa-linkedin'],
                                        'Outros' => ['class' => 'fas', 'icon' => 'fa-external-link-alt']
                                    ];
                                    foreach ($fontesTrafego as $fonte): 
                                        $icone = $icones[$fonte['fonte']] ?? ['class' => 'fas', 'icon' => 'fa-link'];
                                    ?>
                                        <tr>
                                            <td>
                                                <i class="<?= $icone['class'] ?> <?= $icone['icon'] ?> source-icon"></i>
                                                <?= htmlspecialchars($fonte['fonte']) ?>
                                            </td>
                                            <td style="text-align: right;">
                                                <span class="badge-count"><?= number_format($fonte['visitas'], 0, ',', '.') ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="2" style="text-align: center; padding: 40px; color: #999;">
                                            <i class="fas fa-link" style="font-size: 48px; margin-bottom: 10px; display: block;"></i>
                                            Nenhum dado disponível ainda
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('visitasChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($datas) ?>,
                datasets: [
                    {
                        label: 'Visualizações',
                        data: <?= json_encode($pageviewsPorDia) ?>,
                        borderColor: '#007bff',
                        backgroundColor: 'rgba(0, 123, 255, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Visitantes Únicos',
                        data: <?= json_encode($visitantesPorDia) ?>,
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        tension: 0.4,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
