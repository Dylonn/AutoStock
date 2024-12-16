<?php
    session_start();
    require_once 'configF.php';

    if($_SERVER['REQUEST_METHOD'] == 'POST'){


      $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            die("Conexão falhou: " . $conn->connect_error);
        }

        $nome = $_POST['nome'];
        $email = $_POST['email'];
        $senha = $_POST['senha'];
        $cpf = $_POST['cpfEcnpj'];

        $sql = "INSERT INTO usuarios (nome_usuario, email, senha,cpfEcnpj)
            VALUES ('$nome', '$email', '$senha', '$cpf')";

        if ($conn->query($sql) === TRUE) {
         echo "<script>Cadastro realizado com sucesso!</script>";
          header("Location: tela3.php");
        } else {
             echo "<script>Erro ao cadastrar: </script>" . $conn->error;
    }

// Fecha a conexão
$conn->close();


    }
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro</title>
    <style>
      /* Shared styles */
body {
  font-family: Arial, sans-serif;
  background-image: url('img/novoFundoCarro.jpg'); /* Replace with your background image */
  background-size: cover;
  background-repeat: no-repeat;
  background-position: center;
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
}

/* Form container */
.cadastro {
  background-color: #fff; /* White background */
  border-radius: 10px;
  box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2); /* Subtle shadow */
  padding: 20px;
  margin: 0 auto; /* Center the form */
  max-width: 400px;
}

/* Heading */
h1 {
  color: #663399; /* Purple heading color */
  text-align: center;
  margin-bottom: 20px;
}

/* Labels and Inputs */
label {
  display: block;
  margin-bottom: 5px;
}

input[type="text"],
input[type="email"],
input[type="password"] {
  width: 100%;
  padding: 10px;
  margin-bottom: 10px;
  border: 1px solid #ccc;
  border-radius: 5px;
}

/* Button */
button {
  background-color: #663399; /* Purple button color */
  color: #fff;
  border: none;
  padding: 10px 20px;
  border-radius: 5px;
  cursor: pointer;
  margin-top: 10px;
}

button:hover {
  background-color: #8040c0; /* Darker purple on hover */
}

/* Additional styling for a slightly different approach */
.cadastro {
  border: 2px solid #ddd; /* Optional border */
}

h1 {
  font-size: 20px; /* Adjust font size if needed */
}

input[type="text"],
input[type="email"],
input[type="password"] {
  background-color: #f0f0f0; /* Light gray background */
}

.b2 { /* Optional class for additional button styling */
  background-color: #333; /* Darker button option */
  color: #fff;
}
    </style>
</head>
<>
    <form action="" method="POST" class="cadastro">
        <h1>Faça o cadastro.</h1>
        <label for="nome">Nome Completo</label>
        <input type="text" id="nome" name="nome" required>
        
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required>
        
        <label for="senha">Senha</label>
        <input type="password" id="senha" name="senha" required>

        <label for="cpf">CPF ou CNPJ</label>
        <input type="text" id="cpfcnpj" name="cpfEcnpj" required >
        
        <button type="submit" class="b2">Cadastrar</button>
    </form>

    <script src="https://cdn.jsdelivr.net/npm/inputmask/dist/inputmask.min.js"></script>
    <script src="mask.js"></script>

    
    

</body>
</html>
