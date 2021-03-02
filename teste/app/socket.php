<?php

namespace MyApp;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Socket implements MessageComponentInterface {

    public function __construct()
    {
        // Cria um objeto para armazenar os browsers.
        $this->clients = new \SplObjectStorage;

        // senha_atual = quantidade de senhas que foram tiradas pelos clientes.
        $this->senha_atual = 0;

        // senha_atendida = quantidade de senhas que foram atendidas pelos funcionarios.
        $this->senha_atendida = 0;
    }

    public function onOpen(ConnectionInterface $conn) {

        // Cada browser que se conectar, é armazenado no mesmo objeto.
        $this->clients->attach($conn);

        echo "New connection! ({$conn->resourceId})\n";

        $senha_atual = array("senha_atual", $this->senha_atual);
        $senha_atendida = array("senha_atendida", $this->senha_atendida);
        $conn->send(json_encode($senha_atual));
        $conn->send(json_encode($senha_atendida));

    }

    public function onMessage(ConnectionInterface $from, $msg) {

        // Recebe a mensagem enviada pelo browser

        if($msg == "pedir_senha") {

            // Aumenta um valor à variavel senha_atual
            $this->senha_atual++;

            // Para cada browser que está ligado ao websocket
            foreach ($this->clients as $client) {

                // Cria uma array com o comando e com a senha atual
                $senha = array("senha_atual", $this->senha_atual);

                // Envia para cada browser a array.
                $client->send(json_encode($senha));
                
            }
        }

        if($msg == "recusar_senha") {

            // Se a senha atentida - 1 for menor ou igual a 0 retorna, para não tornar o valor neutro nem negativo.
            /* Exemplo: 10 - 1 <= 0 - Passa
               Exemplo: 0 - 1 <= 0  - Retorna */
            if($this->senha_atendida - 1 <= 0) return; 

            // Diminui uma senha atendida
            $this->senha_atendida--;

            // Para cada browser que está ligado ao websocket
            foreach ($this->clients as $client) {

                // Cria uma array com o comando e com a senha atendida
                $senha = array("senha_atendida", $this->senha_atendida);

                // Envia para cada browser a array.
                $client->send(json_encode($senha));
                
            }
        }

        if($msg == "atender_senha") {

            // Se a senha atendida + 1 for maior que a senha atual retorna 
            if($this->senha_atendida + 1 > $this->senha_atual) return; 
            
            // Aumenta uma senha atendida
            $this->senha_atendida++;

            // Para cada browser que está ligado ao websocket
            foreach ($this->clients as $client) {

                // Cria uma array com o comando e com a senha atendida
                $senha = array("senha_atendida", $this->senha_atendida);

                // Envia para cada browser a array.
                $client->send(json_encode($senha));
                
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
    }
}
