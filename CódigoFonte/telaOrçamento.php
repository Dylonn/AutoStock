<?php
session_start();
require_once 'configF.php';
require_once 'vendor/tecnickcom/tcpdf/tcpdf.php';  
require_once __DIR__ . '/vendor/autoload.php';

ob_start();  // Começa a captura da saída para evitar erros de "Already Sent"

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
$sql_usuario = "SELECT id_usuario, nome_usuario FROM usuarios WHERE email = ?";
$stmt_usuario = $conn->prepare($sql_usuario);
$stmt_usuario->bind_param("s", $email_usuario);
$stmt_usuario->execute();
$result_usuario = $stmt_usuario->get_result();

if ($result_usuario->num_rows === 0) {
    die("Usuário não encontrado.");
}

$usuario = $result_usuario->fetch_assoc();
$id_usuario = $usuario['id_usuario'];
$nome_usuario = $usuario['nome_usuario'];
$stmt_usuario->close();

// Gera e baixa o PDF
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

ob_end_flush();  // Envia toda a saída acumulada e limpa o buffer
?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orçamento de Serviço</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f2f2f2; }
        h1 { color: #512da8; }
        form { background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px #ccc; }
        label { display: block; margin: 10px 0 5px; }
        input[type="text"], input[type="number"], textarea { width: 100%; padding: 8px; margin-bottom: 10px; }
        button { background-color: #512da8; color: white; padding: 10px 20px; border: none; cursor: pointer; }
        button:hover { background-color: #311b92; }
    </style>
</head>
<body>
    <h1>Cadastro de Orçamento</h1>
    <?php if (isset($_GET['success'])): ?>
        <p style="color: green;">Orçamento salvo com sucesso! <a href="telaOrçamento.php?download_orcamento=<?= $_GET['orcamento_id'] ?>">Baixar PDF</a></p>
    <?php endif; ?>
    <form method="POST">
        <label for="marca">Marca do Veículo:</label>
        <input type="text" name="marca" required>

        <label for="modelo">Modelo:</label>
        <input type="text" name="modelo" required>

        <label for="ano">Ano:</label>
        <input type="number" name="ano" required>

        <label for="placa">Placa:</label>
        <input type="text" name="placa" required>

        <label for="quilometragem">Quilometragem:</label>
        <input type="number" name="quilometragem" required>

        <label for="observacoes">Observações:</label>
        <textarea name="observacoes"></textarea>

       
        <h3>Serviços</h3>
        <div id="servicos">
            <div class="servico">
                <label>Descrição do Serviço:</label>
                <input type="text" name="servicos[0][descricao]" required>
                <label>Valor da Mão de Obra:</label>
                <input type="number" step="0.01" name="servicos[0][valor]" required>
            </div>
        </div>
        <button type="button" onclick="adicionarServico()">Adicionar Serviço</button><br><br>

        <button type="submit">Salvar Orçamento</button>
    
    </form>

    <a href="tela3.php">voltar</a>

    <script>
        function adicionarServico() {
            const servicosDiv = document.getElementById('servicos');
            const index = servicosDiv.children.length;
            const div = document.createElement('div');
            div.classList.add('servico');
            div.innerHTML = `
                <label>Descrição do Serviço:</label>
                <input type="text" name="servicos[${index}][descricao]" required>
                <label>Valor da Mão de Obra:</label>
                <input type="number" step="0.01" name="servicos[${index}][valor]" required>
            `;
            servicosDiv.appendChild(div);
        }
    </script>
</body>
</html>
