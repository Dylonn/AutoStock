<?php

session_start();

require_once 'configF.php';


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}


if (!isset($_SESSION['email'])) {
    die("Você precisa estar logado para acessar esta página.");
}

$email_usuario = $_SESSION['email'];

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
$nome_usuario = $usuario['nome_usuario']; // Captura o nome do usuário
$stmt_usuario->close();


$email_usuario = $_SESSION['email'];

// Busca o ID da empresa associado ao email do usuário
$sql = "SELECT p.id FROM perfilEmpresa p JOIN usuarios u ON p.id_usuario = u.id_usuario WHERE u.email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email_usuario);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("Perfil da empresa não encontrado.");
}

$id_empresa = $result->fetch_assoc()['id'];
$stmt->close();

// Busca agendamentos relacionados à empresa
$sql_agendamentos = "
    SELECT a.*, c.nome, c.email, s.nomeServico
    FROM agendamentos a 
    JOIN cliente c ON a.cliente_id = c.id_cliente 
    JOIN servicos s ON a.servico_id = s.id_servico
    WHERE a.servico_id IN (SELECT id_servico FROM servicoPerfil WHERE id_empresa = ?)
";

$stmt_agendamentos = $conn->prepare($sql_agendamentos);
$stmt_agendamentos->bind_param("i", $id_empresa);
$stmt_agendamentos->execute();
$result_agendamentos = $stmt_agendamentos->get_result();
$agendamentos = $result_agendamentos->fetch_all(MYSQLI_ASSOC);
$stmt_agendamentos->close();


?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Agendamentos</title>
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
    justify-content: flex-start; /* Manter o conteúdo no topo */
    min-height: 100vh;
    text-align: center;
    padding-top: 40px; /* Espaçamento para o topo */
}

h1 {
    color: #512da8;
    margin-bottom: 20px;
}

h2,h3 {
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
    max-width: 1000px; /* Limita a largura máxima da tela */
    padding: 30px;
    margin-top: 20px; /* Adiciona um pequeno espaçamento entre a tabela e o restante */
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
input[type="number"] {
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

table {
    margin-top: 20px;
    border-collapse: collapse;
    width: 80%;
    max-width: 900px;
    margin: 20px auto;
    color: #512da8;
}

th, td {
    border: 2px solid #512da8;
    padding: 10px;
    text-align: center;
    font-family: 'Roboto Mono', monospace;
}

th {
    background-color: #512da8;
    color: #fff;
}

tbody tr:hover {
    background-color: #f2f2f2;
}

th:nth-child(1), td:nth-child(1) { width: 5%; }
th:nth-child(2), td:nth-child(2) { width: 20%; }
th:nth-child(3), td:nth-child(3) { width: 10%; }
th:nth-child(4), td:nth-child(4) { width: 10%; }
th:nth-child(5), td:nth-child(5) { width: 15%; }
th:nth-child(6), td:nth-child(6) { width: 10%; }
th:nth-child(7), td:nth-child(7) { width: 15%; }
th:nth-child(8), td:nth-child(8) { width: 15%; }
th:nth-child(9), td:nth-child(9) { width: 10%; }

.scrollable-column {
    max-width: 190px;
    max-height: 80px;
    overflow-x: auto;
    white-space: nowrap;
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
<h1>Gerenciar Agendamentos</h1>

<h2>Agendamentos</h2>
<h3><?php echo "Sessão iniciada com o valor de e-mail: " . ($_SESSION['email'] ?? "não definida."); ?></h3>
<table border="1">
    <tr>
        <th>Nome do Cliente</th>
        <th>Email do Cliente</th>
        <th>Serviço</th>
        <th>Data do Agendamento</th>
        <th>Status</th>
        <th>Ações</th>
    </tr>
    <?php foreach ($agendamentos as $agendamento): ?>
    <tr>
        <td><?= htmlspecialchars($agendamento['nome']) ?></td>
        <td><?= htmlspecialchars($agendamento['email']) ?></td>
        <td><?= htmlspecialchars($agendamento['nomeServico']) ?></td> <!-- Agora o nome do serviço será mostrado -->
        <td><?= htmlspecialchars($agendamento['data_agendamento']) ?></td>
        <td><?= htmlspecialchars($agendamento['Stats']) ?></td>
        <td>
            <form method="POST" action="processaSolicitacao.php">
                <input type="hidden" name="id_agendamento" value="<?= $agendamento['id_agendamento'] ?>">
                <input type="hidden" name="tipo" value="agendamento">
                <button type="submit" name="acao" value="confirmar">Confirmar</button>
                <button type="submit" name="acao" value="cancelar">Cancelar</button>
                <textarea name="justificativa" placeholder="Justificativa de cancelamento (opcional)"></textarea>
            </form>
        </td>
    </tr>
<?php endforeach; ?>
</table>

<a href="tela3.php">Voltar</a>
</body>
</html>
<?php $conn->close(); ?>
