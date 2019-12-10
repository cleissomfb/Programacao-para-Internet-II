<?php

use \Firebase\JWT\JWT;
use Slim\Psr7\Response;

include_once('Usuario.php');
include_once('UsuarioDAO.php');

class UsuarioController{

    private $secretKey = "sen@c";

    // Listar todos os usuários cadastrados no banco de dados.
    public function listar($request, $response, $args){
            
        try{ 
            $dao= new UsuarioDAO;    
            $usuario = $dao->listar();
        
            return $response->withJSON($usuario)
            ->withStatus(200);
        }catch(Exception $error){
            return $response->withStatus(401);
        }
    }

    // Inserir um novo usuário
    public function inserir($request, $response, $args) {
        $data = $request->getParsedBody();
        
        try{      
            $usuario = new Usuario(0,$data['login_usuario'],$data['senha_usuario'],$data['nome_usuario']);
        
            $dao = new UsuarioDAO;
            $usuario = $dao->inserir($usuario);
        
            return $response->withJson($usuario)
            ->withStatus(200);
        }catch(Exception $error){
            return $response->withStatus(401);
        }
    }

    // Atualiza um usuário
    public function atualizar($request, $response, $args) {
        
        $id = $args['id_usuario'];
        $data = $request->getParsedBody();
        
        try{  
            
            $usuario = new Usuario($id, $data['login_usuario'],$data['senha_usuario'],$data['nome_usuario']);
        
            $dao = new UsuarioDAO;
            $usuario = $dao->atualizar($usuario);
        
            return $response->withJson($usuario)
            ->withStatus(200);

        }catch(Exception $error){
            return $response->withStatus(401);
        }
    }

    // Buscar um usuario pelo id_usuario
    public function buscarPorId($request, $response, $args) {
        $id = $args['id'];
        
        try{
            $dao= new UsuarioDAO;    
            $usuario = $dao->buscarPorId($id);
            
            return $response->withJson($usuario)
            ->withStatus(200);
        }catch(Exception $error){
            return $response->withStatus(401);
        }
    }
    
    // Deletar um usuário pelo id_usuario
    public function deletar($request, $response, $args) {
        $id = $args['id'];
    
        try{
            $dao = new UsuarioDAO;
            $usuario = $dao->deletar($id);

            return $response->withJson($usuario)
            ->withStatus(200);

        }catch(Exception $error){
            return $response->withStatus(401);
        }
    }

    // Buscar um usuario pelo nome
    public function buscarPorNome($request, $response, $args){
        $nome = $args['nome_usuario'];

        try{
            $dao = new UsuarioDAO;
            $usuario = $dao->listarPorNome($nome);

            return $response->withJSON($usuario)
            ->withStatus(200);
        }catch(Exception $error){
            return $response->withStatus(401);
        }
    }

    // Atutentica o usuário para poder vizualizar, editar e deletar.
    public function autenticar($request, $response, $args) {
        $user = $request->getParsedBody();
            
        $dao = new UsuarioDAO;    
        $usuario = $dao->buscarPorLogin($user['login_usuario']);
        if($usuario->senha_usuario == $user['senha_usuario']) {
            $token = array(
                'user' => strval($usuario->id_usuario),
                'nome' => $usuario->nome_usuario,
                'login' => $usuario->login_usuario,
                'senha' => $usuario->senha_usuario,
            );
            $jwt = JWT::encode($token, $this->secretKey);
            return $response->withJson(["token" => $jwt, "nome_usuario" => $usuario->nome_usuario])
                ->withHeader('Content-type', 'application/json')->withStatus(201);
        }else
            return $response->withStatus(401);

    }

    // Valida o token criado no metodo autenticar() o usuário para poder vizualizar, editar e deletar.
    //OBs: Para funcionar a validação, copiar o codigo token e ir no postman no item "Heders", no campo "Key"
    //Digitar "Authorization" e no campo value, colar o codigo token.
    public function validarToken($request, $handler){
        $response = new Response();
        $token = $request->getHeader('Authorization');
            
        if($token && $token[0]){
            try {
                $decoded = JWT::decode($token[0], $this->secretKey, array('HS256'));

                if($decoded){
                    $response = $handler->handle($request);
                    return($response->withStatus(200));
                }
            } catch(Exception $error) {
                return $response->withStatus(401);
            }
        }
        return $response->withStatus(401);
    }
}

?>