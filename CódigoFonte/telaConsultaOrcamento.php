<?php
session_start();
require_once 'configF.php';
require_once 'vendor/tecnickcom/tcpdf/tcpdf.php';  
require_once __DIR__ . '/vendor/autoload.php';

// Inicia a captura de saída antes de qualquer HTML
ob_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['email'])) {
    header("Location: tela1.php"); // Redireciona para a página de login
    exit();
}

// Conexão com o banco de dados
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// Obtém o email do usuário logado
$email_usuario = $_SESSION['email'];

// Busca o ID do usuário logado
$sql_usuario = "SELECT id_usuario FROM usuarios WHERE email = ?";
$stmt_usuario = $conn->prepare($sql_usuario);
$stmt_usuario->bind_param("s", $email_usuario);
$stmt_usuario->execute();
$result_usuario = $stmt_usuario->get_result();

if ($result_usuario->num_rows === 0) {
    die("Usuário não encontrado.");
}

$usuario = $result_usuario->fetch_assoc();
$id_usuario = $usuario['id_usuario'];
$stmt_usuario->close();

// Consulta todos os orçamentos feitos pelo usuário
$sql_orcamentos = "SELECT * FROM orcamento WHERE id_usuario = ?";
$stmt_orcamentos = $conn->prepare($sql_orcamentos);
$stmt_orcamentos->bind_param("i", $id_usuario);
$stmt_orcamentos->execute();
$result_orcamentos = $stmt_orcamentos->get_result();

// Excluir orçamento
if (isset($_GET['excluir_orcamento'])) {
    $id_orcamento_excluir = $_GET['excluir_orcamento'];

    // Excluir serviços relacionados ao orçamento
    $sql_servicos = "DELETE FROM servicos_orcamento WHERE id_orcamento = ?";
    $stmt_servicos = $conn->prepare($sql_servicos);
    $stmt_servicos->bind_param("i", $id_orcamento_excluir);
    $stmt_servicos->execute();

    // Agora excluir o orçamento
    $sql_orcamento = "DELETE FROM orcamento WHERE id_orcamento = ?";
    $stmt_orcamento = $conn->prepare($sql_orcamento);
    $stmt_orcamento->bind_param("i", $id_orcamento_excluir);
    $stmt_orcamento->execute();

    // Redireciona de volta para a página de consulta
    header("Location: telaConsultaOrcamento.php");
    exit();
}

// Baixar PDF
if (isset($_GET['download_orcamento'])) {
    $id_orcamento = $_GET['download_orcamento'];
    
    // Consulta o orçamento
    $sql = "SELECT * FROM orcamento WHERE id_orcamento = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_orcamento);
    $stmt->execute();
    $orcamento = $stmt->get_result()->fetch_assoc();
    
    // Consulta os serviços
    $sql_servicos = "SELECT descricao_servico, valor_mao_obra FROM servicos_orcamento WHERE id_orcamento = ?";
    $stmt = $conn->prepare($sql_servicos);
    $stmt->bind_param("i", $id_orcamento);
    $stmt->execute();
    $servicos = $stmt->get_result();
    
    // Criação do objeto PDF
    $pdf = new TCPDF();
    $pdf->AddPage();
    
    // Definindo o título
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'Orçamento #'. $orcamento['id_orcamento'], 0, 1, 'C');
    
    // Definindo o conteúdo do orçamento
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Ln(10);

    // Criando uma tabela com os dados do orçamento
    $pdf->SetFillColor(220, 220, 220);
    $pdf->Cell(40, 10, 'Veículo', 1, 0, 'C', 1);
    $pdf->Cell(70, 10, $orcamento['veiculo_marca'] . " " . $orcamento['veiculo_modelo'], 1, 1, 'L');
    
    $pdf->Cell(40, 10, 'Ano', 1, 0, 'C', 1);
    $pdf->Cell(70, 10, $orcamento['veiculo_ano'], 1, 1, 'L');
    
    $pdf->Cell(40, 10, 'Placa', 1, 0, 'C', 1);
    $pdf->Cell(70, 10, $orcamento['placa'], 1, 1, 'L');
    
    $pdf->Cell(40, 10, 'Quilometragem', 1, 0, 'C', 1);
    $pdf->Cell(70, 10, $orcamento['quilometragem'] . ' km', 1, 1, 'L');
    
    $pdf->Ln(5);
    
    // Título da seção de serviços
    $pdf->Cell(0, 10, 'Serviços:', 0, 1, 'L');
    
    // Cabeçalho da tabela de serviços
    $pdf->SetFillColor(200, 200, 200);
    $pdf->Cell(110, 10, 'Descrição do Serviço', 1, 0, 'C', 1);
    $pdf->Cell(40, 10, 'Valor Mão de Obra', 1, 1, 'C', 1);
    
    // Adicionando os serviços à tabela
    while ($servico = $servicos->fetch_assoc()) {
        $pdf->Cell(110, 10, $servico['descricao_servico'], 1, 0, 'L');
        $pdf->Cell(40, 10, "R$ " . number_format($servico['valor_mao_obra'], 2, ',', '.'), 1, 1, 'R');
    }

    // Forçando o download do PDF
    $pdf->Output('orcamento_' . $id_orcamento . '.pdf', 'D');
    exit();
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta de Orçamentos</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f2f2f2; }
        h1 { color: #512da8; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 10px; text-align: left; }
        button { background-color: #512da8; color: white; padding: 5px 10px; border: none; cursor: pointer; }
        button:hover { background-color: #311b92; }
    </style>
</head>
<body>
    <h1>Consulta de Orçamentos</h1>
    
    <?php if ($result_orcamentos->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Veículo</th>
                    <th>Valor Total</th>
                    <th>Data</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($orcamento = $result_orcamentos->fetch_assoc()): ?>
                    <tr>
                        <td><?= $orcamento['id_orcamento'] ?></td>
                        <td><?= $orcamento['veiculo_marca'] . " " . $orcamento['veiculo_modelo'] ?></td>
                        <td>R$ <?= number_format($orcamento['valor_total'], 2, ',', '.') ?></td>
                        <td><?= date("d/m/Y H:i:s", strtotime($orcamento['data_orcamento'])) ?></td>
                        <td>
                            <a href="telaConsultaOrcamento.php?download_orcamento=<?= $orcamento['id_orcamento'] ?>">
                                <button>Baixar PDF</button>
                            </a>
                            <a href="telaConsultaOrcamento.php?excluir_orcamento=<?= $orcamento['id_orcamento'] ?>" onclick="return confirm('Tem certeza que deseja excluir este orçamento?')">
                                <button>Excluir</button>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Você ainda não possui orçamentos registrados.</p>
    <?php endif; ?>

    <a href="tela3.php">Voltar</a>
</body>
</html>

<?php
$conn->close();
?>
