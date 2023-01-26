<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\TbtopicosModel;

class Topicos extends BaseController
{
    private $topicosModel;       
    
    public function __construct()
    {
        $this->topicosModel = new tbtopicosModel();
    }

    public function dadosAtas()
    {
        $db = db_connect();
        $builder = $db->table('tbatas a');
        $builder->select('a.cod, a.data, a.descricao, st.descricao as descsetor');
        $builder->join('tbsetores st', 'st.cod = a.codsetor');
        $builder->orderBy('a.cod', 'DESC');
        $query = $builder->get();

        return $query->getResultArray();
    }

    public function dadosReuniao($codSetor)
    {
        $db = db_connect();
        $builder = $db->table('tbtopicos t');
        $builder->select('a.data, st.descricao as descsetor, t.cod as codt, t.assunto, t.providencia, e.nome, s.descricao as descstatus, t.diretoria, a.cod as coda, a.descricao as descata, a.participantes');
        $builder->join('tbenvolvidos e', 'e.cod = t.codresponsavel');
        $builder->join('tbstatus s', 's.cod = t.codstatus');
        $builder->join('tbatas a', 'a.cod = t.codata');
        $builder->join('tbsetores st', 'st.cod = a.codsetor');
        $builder->where('st.cod', $codSetor);
        $builder->orderBy('t.cod', 'DESC');
        $query = $builder->get();

        return $query->getResultArray();
    }

    public function dadosEnvolvidos()
    {
        $db = db_connect();
        $builder = $db->table('tbenvolvidos e');
        $builder->select('e.cod, e.nome');
        $query = $builder->get();

        return $query->getResultArray();
    }

    public function dadosStatus()
    {
        $db = db_connect();
        $builder = $db->table('tbstatus st');
        $builder->select('st.cod, st.descricao ');
        $query = $builder->get();

        return $query->getResultArray();
    }

    public function dadosSetor($codTopico)
    {
        $db = db_connect();
        $builder = $db->table('tbsetores s');
        $builder->select('s.descricao');
        $builder->join('tbatas a', 'a.codsetor = s.cod');
        $builder->join('tbtopicos t', 't.codata = a.cod');
        $builder->where('t.cod', $codTopico);
        $query = $builder->get();

        return $query->getResultArray();
    }

    public function index()
    {
        //Pegando dados da URL, pois não existe formulario
        $url_anterior = $_SERVER['HTTP_REFERER'];
        $url_partes = explode('/', $url_anterior);
        $setor = substr($url_partes[6],7);

        return view('topicos',[
            'topicos_atas' => $this->dadosAtas(),
            'envolvidos'   => $this->dadosEnvolvidos(),
            'status'       => $this->dadosStatus(),
            'setor'        => $setor
        ]);
    }

    public function topicosComercial()
    {
        return view('grid_reunioes',[
            'grid_reunioes' => $this->dadosReuniao(2),
            'setor' => 'Comercial'
        ]);

    }

    public function topicosAudiplanner()
    {
        return view('grid_reunioes',[
            'grid_reunioes' => $this->dadosReuniao(1),
            'setor' => 'Audiplanner'
        ]);
    }
    
    public function topicosDiretoriaFinanceiro()
    {
        return view('grid_reunioes',[
            'grid_reunioes' => $this->dadosReuniao(3),
            'setor' => 'Diretoria/Financeiro'
        ]); 
    }    

    public function salvar()
    {

        //Pegando dados do input, pois existe formulario    
        //$request = \Config\Services::request();
        $setor = $this->request->getVar('inpSetor');        

        $this->topicosModel->save($this->request->getPost());

        echo view('mensagens', [
            'mensagem' => 'Tópico Salvo com Sucesso',
            'tipoMensagem'  => 'is-success',
            'link' => 'public/Topicos/topicos'.$setor.'/'
        ]);

    }

    public function apagar($cod)
    {
        //Pegando dados da QRY, pois não existe formulario        
        $setor = $this->dadosSetor($cod);

        $this->topicosModel->where('cod', $cod)->delete();

        echo view('mensagens', [
            'mensagem' => 'Registro Excluído com Sucesso',
            'tipoMensagem'  => 'is-success',
            'link' => 'public/Topicos/topicos'.str_replace("/", "", $setor[0]['descricao']).'/'
        ]);
    }

    public function editar($cod)
    {
        $setor = $this->dadosSetor($cod);

        return view('topicos', [
            'dados_topicos' => $this->topicosModel->find($cod),
            'setor'         => str_replace("/", "", $setor[0]['descricao'])
        ]);
    }
}