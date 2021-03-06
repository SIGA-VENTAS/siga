<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class gastos extends MY_Controller
{

    function __construct()
    {
        parent::__construct();
        if ($this->login_model->verify_session()) {
            $this->load->model('gastos/gastos_model');
            $this->load->model('tiposdegasto/tipos_gasto_model');
            $this->load->model('local/local_model');
            $this->load->model('monedas/monedas_model');
            $this->load->model('cajas/cajas_model');
            $this->load->model('proveedor/proveedor_model');
        } else {
            redirect(base_url(), 'refresh');
        }
    }


    /** carga cuando listas los proveedores*/
    function index()
    {

        if ($this->session->flashdata('success') != FALSE) {
            $data ['success'] = $this->session->flashdata('success');
        }
        if ($this->session->flashdata('error') != FALSE) {
            $data ['error'] = $this->session->flashdata('error');
        }

        $data['locales'] = $this->local_model->get_local_by_user($this->session->userdata('nUsuCodigo'));
        $data['tipos_gastos'] = $this->tipos_gasto_model->get_all();
        $data["proveedores"] = $this->proveedor_model->select_all_proveedor();
        $data["usuarios"] = $this->db->get_where('usuario', array('activo' => 1))->result();
        $data['monedas'] = $this->db->get_where('moneda', array('status_moneda' => 1))->result();


        $dataCuerpo['cuerpo'] = $this->load->view('menu/gastos/gastos', $data, true);

        if ($this->input->is_ajax_request()) {
            echo $dataCuerpo['cuerpo'];
        } else {
            $this->load->view('menu/template', $dataCuerpo);
        }
    }

    function lista_gasto()
    {

        $date_range = explode(" - ", $this->input->post('fecha'));
        $fecha_ini = str_replace("/", "-", $date_range[0]);
        $fecha_fin = str_replace("/", "-", $date_range[1]);

        $params = array(
            'local_id' => $this->input->post('local_id'),
            'fecha_ini' => date('Y-m-d H:i:s', strtotime($fecha_ini . ' 00:00:00')),
            'fecha_fin' => date('Y-m-d H:i:s', strtotime($fecha_fin . ' 23:59:59')),
            'persona_gasto' => $this->input->post('persona_gasto'),
            'id_moneda' => $this->input->post('moneda_id'),
            'status_gastos' => $this->input->post('estado_id'),
        );

        $tipo_gasto = $this->input->post('tipo_gasto');
        if ($tipo_gasto != "-")
            $params['tipo_gasto'] = $tipo_gasto;

        $persona_gasto = $this->input->post('persona_gasto');
        if ($persona_gasto == 1) {
            $proveedor = $proveedor = $this->input->post('proveedor');
            if ($proveedor != "-")
                $params['proveedor'] = $proveedor;
        }
        if ($persona_gasto == 2) {
            $usuario = $usuario = $this->input->post('usuario');
            if ($usuario != "-")
                $params['usuario'] = $usuario;
        }

        $data['moneda'] = $this->db->get_where('moneda', array('id_moneda' => $params['id_moneda']))->row();
        $data['gastoss'] = $this->gastos_model->get_all($params);
        $data['gastos_totales'] = $this->gastos_model->get_totales_gasto($params);

        $this->load->view('menu/gastos/gasto_lista', $data);
    }

    function form($id = FALSE)
    {

        $data = array();
        $data['gastos'] = array();
        $data['tiposdegasto'] = $this->tipos_gasto_model->get_all();
        $data['local'] = $this->local_model->get_local_by_user($this->session->userdata('nUsuCodigo'));
        $data["monedas"] = $this->monedas_model->get_all();
        $data["proveedores"] = $this->proveedor_model->select_all_proveedor();
        $data["usuarios"] = $this->db->get_where('usuario', array('activo' => 1))->result();
        $data["documentos"] = $this->db->get_where('documentos', array('gastos' => 1))->result();
        $data['cuentas'] = $this->db->select('caja_desglose.*, caja.local_id, caja.moneda_id, moneda.nombre AS moneda_nombre')
            ->from('caja_desglose')
            ->join('caja', 'caja.id = caja_desglose.caja_id')
            ->join('moneda', 'moneda.id_moneda = caja.moneda_id')
            ->where('moneda.status_moneda', 1)
            ->get()->result();

        if ($id != FALSE) {
            $data['gastos'] = $this->gastos_model->get_by('id_gastos', $id);
        }
        $this->load->view('menu/gastos/form', $data);
    }

    function guardar()
    {

        $id = $this->input->post('id');

        $persona_gasto = $this->input->post('persona_gasto');
        if ($persona_gasto == 1) {
            $proveedor = $this->input->post('proveedor');
            $usuario = NULL;
        } elseif ($persona_gasto == 2) {
            $proveedor = NULL;
            $usuario = $this->input->post('usuario');
        }


        $gastos = array(
            'fecha' => date('Y-m-d', strtotime($this->input->post('fecha'))) . " " . date("H:i:s"),
            'fecha_registro' => date('Y-m-d H:i:s'),
            'descripcion' => $this->input->post('descripcion'),
            'total' => $this->input->post('total'),
            'tipo_gasto' => $this->input->post('tipo_gasto'),
            'local_id' => $this->input->post('filter_local_id'),
            'gasto_usuario' => $this->session->userdata('nUsuCodigo'),
            'cuenta_id' => $this->input->post('cuenta_id'),
            'proveedor_id' => $proveedor,
            'usuario_id' => $usuario,
            'responsable_id' => $this->session->userdata('nUsuCodigo'),
            'gravable' => $this->input->post('gravable'),
            'id_documento' => $this->input->post('cboDocumento'),
            'serie' => $this->input->post('doc_serie'),
            'numero' => $this->input->post('doc_numero')
        );

        if (empty($id)) {
            $resultado = $this->gastos_model->insertar($gastos);


        }

        if ($resultado != FALSE) {
            $json['success'] = 'Solicitud Procesada con exito';
        } else {
            $json['error'] = 'Ha ocurrido un error al procesar la solicitud';
        }

        echo json_encode($json);

    }


    function eliminar()
    {
        $id = $this->input->post('id');


        $this->db->where('ref_id', $id);
        $this->db->where('tipo', 'GASTOS');
        $this->db->where('IO', 2);
        $this->db->where('estado', 0);
        $this->db->delete('caja_pendiente');

        $this->db->where('id_gastos', $id);
        $this->db->where('status_gastos', 1);
        $this->db->delete('gastos');

        $json['success'] = 'Se ha eliminado exitosamente';

        echo json_encode($json);
    }

    function historial_pdf()
    {
        $get = json_decode($this->input->get('data'));
        $date_range = explode(" - ", $get->fecha);
        $fecha_ini = str_replace("/", "-", $date_range[0]);
        $fecha_fin = str_replace("/", "-", $date_range[1]);

        $params = array(
            'local_id' => $get->local_id,
            'fecha_ini' => date('Y-m-d H:i:s', strtotime($fecha_ini . ' 00:00:00')),
            'fecha_fin' => date('Y-m-d H:i:s', strtotime($fecha_fin . ' 23:59:59')),
            'persona_gasto' => $get->persona_gasto,
            'id_moneda' => $get->moneda_id,
            'status_gastos' => $get->estado_id,
        );

        $tipo_gasto = $get->tipo_gasto;
        if ($tipo_gasto != "-")
            $params['tipo_gasto'] = $tipo_gasto;

        $persona_gasto = $get->persona_gasto;
        if ($persona_gasto == 1) {
            $proveedor = $proveedor = $get->proveedor;
            if ($proveedor != "-")
                $params['proveedor'] = $proveedor;
        }
        if ($persona_gasto == 2) {
            $usuario = $usuario = $get->usuario;
            if ($usuario != "-")
                $params['usuario'] = $usuario;
        }

        $data['moneda'] = $this->db->get_where('moneda', array('id_moneda' => $params['id_moneda']))->row();
        $data['gastoss'] = $this->gastos_model->get_all($params);
        $data['gastos_totales'] = $this->gastos_model->get_totales_gasto($params);

        $data['fecha_ini'] = $params['fecha_ini'];
        $data['fecha_fin'] = $params['fecha_fin'];

        $this->load->library('mpdf53/mpdf');
        $mpdf = new mPDF('utf-8', 'A4', 0, '', 5, 5, 5, 5, 5, 5);
        $html = $this->load->view('menu/gastos/gasto_lista_pdf', $data, true);
        $mpdf->WriteHTML($html);
        $mpdf->Output();
    }


    function historial_excel()
    {
        $get = json_decode($this->input->get('data'));
        $date_range = explode(" - ", $get->fecha);
        $fecha_ini = str_replace("/", "-", $date_range[0]);
        $fecha_fin = str_replace("/", "-", $date_range[1]);

        $params = array(
            'local_id' => $get->local_id,
            'fecha_ini' => date('Y-m-d H:i:s', strtotime($fecha_ini . ' 00:00:00')),
            'fecha_fin' => date('Y-m-d H:i:s', strtotime($fecha_fin . ' 23:59:59')),
            'persona_gasto' => $get->persona_gasto,
            'id_moneda' => $get->moneda_id,
            'status_gastos' => $get->estado_id,
        );

        $tipo_gasto = $get->tipo_gasto;
        if ($tipo_gasto != "-")
            $params['tipo_gasto'] = $tipo_gasto;

        $persona_gasto = $get->persona_gasto;
        if ($persona_gasto == 1) {
            $proveedor = $proveedor = $get->proveedor;
            if ($proveedor != "-")
                $params['proveedor'] = $proveedor;
        }
        if ($persona_gasto == 2) {
            $usuario = $usuario = $get->usuario;
            if ($usuario != "-")
                $params['usuario'] = $usuario;
        }

        $data['moneda'] = $this->db->get_where('moneda', array('id_moneda' => $params['id_moneda']))->row();
        $data['gastoss'] = $this->gastos_model->get_all($params);
        $data['gastos_totales'] = $this->gastos_model->get_totales_gasto($params);

        $data['fecha_ini'] = $params['fecha_ini'];
        $data['fecha_fin'] = $params['fecha_fin'];

        echo $this->load->view('menu/gastos/gasto_lista_excel', $data, true);
    }


}
