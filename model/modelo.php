<?php



interface DbConnectionInterface
{
    public function connect();
    public function query();
}

//////////////////////////
////////Classe Usuario
//////////////////////

class Usera 
{

    private $id;
    private $nome; 
    private $passworda;
    private $portA;
    private $portB;  
    

    public function __construct($pid, $pnome, $ppassworda, $pportA, $pportB) {

        $this->id = $pid;
        $this->nome = $pnome;
        $this->passworda = $ppassworda;
        $this->portA = $pportA;
        $this->portB = $pportB;
       
    }

    //metodos magicos

     public function getId(){
        return $this->id;

     }
     public function getName(){
        return $this->nome;

     }
    
   
     public function getPortA(){
        return $this->portA;

     }

     public function getPortB(){
        return $this->portB;

     }
     public function getSenha(){
      return $this->passworda;

   }
}




///conectar MYSQL
class MySqlConnection implements DbConnectionInterface{
    private $conn;
    public function connect()
    {
        $host = 'localhost';
        $user = 'ott';
        $password = 'catrota';
        $database = 'cloud';


       // EcONEXÃO
        $this->conn = new mysqli($host, $user, $password, $database);
   	$this->conn->set_charset("utf8mb4");
        if ($this->conn->connect_error) {
            // In a real application, you'd log this, not echo
            die("falha NA CONEXÃO: " . $this->conn->connect_error);
        }
        //echo "Conectado com sucesso.\n";
    }
     public function getConnection() {
        return $this->conn;
    }

///////////////////////////
////INSERIR USUARIO
/////////////
   public function insertUser(Usera $usuario) {
    $id = $usuario->getId();
    $nome = $usuario->getName();
    $portA = $usuario->getPortA();
   $portB = $usuario->getPortB();
    $password = $usuario->getSenha();
    
    
    $sql = "INSERT INTO user (nome, passworda, portA, portB) VALUES (?, ?, ?, ?)";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("ssii", $nome, $password, $portA, $portB);

    if ($stmt->execute()) {
        echo "Usuário inserido com sucesso!";
    } else {
        echo "Erro ao inserir usuário: " . $stmt->error;
    }

    $stmt->close();
}
//////////////////////////////////////////////
 public function searcha(Usera $user)   
    {

          $username = $user->getName();
          $password = $user->getSenha();
        // CRÍTICO: SELECIONAR O ID DO USUÁRIO
        $sql = "SELECT id, nome, passworda, role FROM user WHERE nome = ?";

        
        $stmt = $this->conn->prepare($sql);

        if ($stmt === false) { 
            // Em desenvolvimento, mantenha o log de erro
            error_log("Login prepare falhou: " . $this->conn->error);
            return false;
        }

        $stmt->bind_param("s", $username);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows !== 1) {
            $stmt->close();
            return false; // Usuário não encontrado
        }

        $dados = $resultado->fetch_assoc();
        $stmt->close();

        // ⚠️ USANDO SENHA EM TEXTO PLANO. MUDAR PARA password_verify()
        // Se a senha estiver em texto plano:
        if (password_verify($password, $dados['passworda'])){
       
            return $dados; // Login de sucesso: Retorna [id, nome, passworda]
        }
        
        return false; // Senha inválida
    }


/////////////////////////////////////////////////////////////////////////////////
         public function query()
         {
            $sql = "SELECT * FROM user";
            $result = $this->conn->query($sql);

            if ($result->num_rows > 0) {
                 while ($row = $result->fetch_assoc()) {
                echo "ID: " . $row["id"] . " - Nome: " . $row["nome"] . "<br>";
            }
         }       else {
            echo "Nenhum resultado encontrado.";
            }
        }

    }
class SearchQueryModelo {
    public static function ver(Usera $usuario) {
        echo "ID:"     . $usuario->getId();
        echo "Nome: " . $usuario->getName() . "<br>";
        echo "Senha: " . $usuario->getSenha() . "<br>";
    }
}
       

?>
  







