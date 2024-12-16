<?php
session_start();
require_once 'configF.php';

// Verifica se o cliente está logado
if (!isset($_SESSION['id_cliente'])) {
    header("Location: loginCliente.php");  // Redireciona para a página de login do cliente
    exit();
}

// Verifica se o ID do serviço está presente na URL
$id_servico = $_GET['id_servico'] ?? null;
if (!$id_servico) {
    die("Serviço não especificado.");  // Interrompe a execução se não houver ID
}

// Conexão com o banco de dados
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// Verificar se o serviço existe na tabela 'servicos'
$sql = "SELECT * FROM servicos WHERE id_servico = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_servico);
$stmt->execute();
$result = $stmt->get_result();
$servico = $result->fetch_assoc();

if (!$servico) {
    die("Serviço não encontrado.");  // Valida se o serviço existe na tabela 'servicos'
}
$stmt->close();

// Processar agendamento
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_cliente = $_SESSION['id_cliente'];
    $data_agendamento = $_POST['data_agendamento'] ?? null;
    $horario_agendamento = $_POST['horario_agendamento'] ?? null;
    
    if (!$data_agendamento || !$horario_agendamento) {
        die("Data e horário são obrigatórios.");
    }

    // Concatenar data e horário para formar o valor completo
    $data_horario_completo = $data_agendamento . ' ' . $horario_agendamento;
    
    // Verifique se o formato da data/hora está correto
    if (DateTime::createFromFormat('Y-m-d H:i', $data_horario_completo) === false) {
        die("Formato de data ou horário inválido.");
    }

    // Inserir o agendamento no banco de dados
    $sql = "INSERT INTO agendamentos (cliente_id, servico_id, stats, data_agendamento) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    // Definir o status do agendamento como 'pendente'
    $stats = 'pendente';
    $stmt->bind_param("iiss", $id_cliente, $id_servico, $stats, $data_horario_completo);

    if ($stmt->execute()) {
        // Exibir o pop-up de confirmação e redirecionar para MenuEmpresa.php
        echo "<script>
                alert('Agendamento realizado com sucesso! Aguarde a confirmação.');
                window.location.href = 'MenuEmpresa.php';
              </script>";
    } else {
        echo "<script>
                alert('Erro ao registrar o agendamento: " . $stmt->error . "');
                window.location.href = 'MenuEmpresa.php';
              </script>";
    }
    $stmt->close();
    $conn->close();
    exit();
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Agendar Serviço</title>
</head>
<style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@400;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Montserrat', sans-serif;
        }

        body {
            background-image: url('img/.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-color: #c9d6ff;
            background: linear-gradient(to right, #e2e2e2, #c9d6ff);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            min-height: 100vh;
            text-align: center;
            padding-top: 40px;
        }

        h1 {
            color: #512da8;
            margin-bottom: 20px;
        }

        h2 {
            color: #512da8;
            margin-top: 40px;
            margin-bottom: 20px;
        }

        .container {
            background-color: #fff;
            border-radius: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.35);
            position: relative;
            overflow: hidden;
            width: 80%;
            max-width: 1000px;
            padding: 30px;
            margin-top: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        label {
            margin-top: 10px;
            font-weight: 600;
        }

        input[type="text"],
        input[type="number"],
        input[type="date"],
        input[type="time"] {
            background-color: #eee;
            border-color: #512da8;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 10px;
            outline: none;
            font-size: 13px;
            font-family: 'Roboto Mono', monospace;
            width: 45%;
            text-align: center;
        }

        button {
            background-color: #512da8;
            color: #fff;
            padding: 10px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 10px;
            transition: background-color 0.3s;
            font-weight: 600;
            width: 50%;
        }

        button:hover {
            background-color: #673ab7;
        }

        .message.success, .message.error {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            background-color: #512da8;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            margin-top: 10px;
            transition: background-color 0.3s, opacity 0.3s;
            opacity: 1;
        }

        .message.hidden {
            opacity: 0;
            pointer-events: none;
        }

        a {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            color: #512da8;
            font-weight: bold;
        }

        a:hover {
            text-decoration: underline;
        }

        #botCadastroVoltar {
            display: inline-block;
            padding: 15px 30px;
            font-size: 18px;
            text-align: center;
            text-decoration: none;
            color: #fff;
            background-color: #512da8;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            position: fixed;
            top: 3%;
            right: 5%;
        }

        #botCadastroVoltar:hover {
            background-color: #311b92;
        }
    </style>
<body>
<h1>Agendar Serviço</h1>
<p>Serviço: <?= htmlspecialchars($servico['nomeServico']) ?></p>
<p>Preço: R$ <?= number_format($servico['preco'], 2, ',', '.') ?></p>

<form method="POST">
    <label for="data_agendamento">Data:</label>
    <input type="date" id="data_agendamento" name="data_agendamento" required><br><br>
    
    <label for="horario_agendamento">Horário:</label>
    <input type="time" id="horario_agendamento" name="horario_agendamento" required><br><br>
    
    <button type="submit">Confirmar Agendamento</button>
</form>
<a href="menuCliente.php">Cancelar</a>
</body>
</html>

<?php
$conn->close();
?>
