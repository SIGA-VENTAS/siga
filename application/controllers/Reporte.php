<?php

class Reporte extends MY_Controller
{
    function __construct()
    {
        parent::__construct();
        if ($this->login_model->verify_session()) {
            $this->load->model('local/local_model');
            $this->load->model('producto/producto_model');
            $this->load->model('monedas/monedas_model');
            $this->load->model('reporte/reporte_model');
            $this->load->model('usuario/usuario_model');
            $this->load->model('diccionario_termino/diccionario_termino_model');
            $this->load->model('clientesgrupos/clientes_grupos_model');
        }else{
            redirect(base_url(), 'refresh');
        }
    }

    function productoVendido($action = '')
    {
        switch ($action) {
            case 'filter': {
                $params['local_id'] = $this->input->post('local_id');
                $params['marca_id'] = $this->input->post('marca_id');
                $params['grupo_id'] = $this->input->post('grupo_id');
                $params['familia_id'] = $this->input->post('familia_id');
                $params['linea_id'] = $this->input->post('linea_id');
                $params['producto_id'] = $this->input->post('producto_id');
                $date_range = explode(" - ", $this->input->post('fecha'));
                $params['fecha_ini'] = date('Y-m-d 00:00:00', strtotime(str_replace("/", "-", $date_range[0])));
                $params['fecha_fin'] = date('Y-m-d 23:59:59', strtotime(str_replace("/", "-", $date_range[1])));
                $params['tipo'] = $this->input->post('tipo');
                $data['lists'] = $this->reporte_model->getProductoVendido($params);

                $this->load->view('menu/reportes/productoVendido_list', $data);
                break;
            }
            case 'grafico': {
                $params['local_id'] = $this->input->post('local_id');
                $params['marca_id'] = $this->input->post('marca_id');
                $params['grupo_id'] = $this->input->post('grupo_id');
                $params['familia_id'] = $this->input->post('familia_id');
                $params['linea_id'] = $this->input->post('linea_id');
                $params['producto_id'] = $this->input->post('producto_id');
                $date_range = explode(" - ", $this->input->post('fecha'));
                $params['fecha_ini'] = date('Y-m-d 00:00:00', strtotime(str_replace("/", "-", $date_range[0])));
                $params['fecha_fin'] = date('Y-m-d 23:59:59', strtotime(str_replace("/", "-", $date_range[1])));
                $params['tipo'] = $this->input->post('tipo');
                $params['limit'] = $this->input->post('limit');
                $data['lists'] = $this->reporte_model->getProductoVendido($params);
                echo json_encode($data);
                break;
            }
            case 'pdf': {
                $params = json_decode($this->input->get('data'));
                $date_range = explode(' - ', $params->fecha);
                $input = array(
                    'local_id' => $params->local_id,
                    'marca_id' => $params->marca_id,
                    'grupo_id' => $params->grupo_id,
                    'familia_id' => $params->familia_id,
                    'linea_id' => $params->linea_id,
                    'producto_id' => $params->producto_id,
                    'fecha_ini' => date('Y-m-d 00:00:00', strtotime(str_replace("/", "-", $date_range[0]))),
                    'fecha_fin' => date('Y-m-d 23:59:59', strtotime(str_replace("/", "-", $date_range[1]))),
                    'tipo' => $this->input->post('tipo')
                );

                $data['lists'] = $this->reporte_model->getProductoVendido($input);

                $local = $this->db->get_where('local', array('int_local_id' => $input['local_id']))->row();
                $data['local_nombre'] = $local->local_nombre;
                $data['local_direccion'] = $local->direccion;

                $data['fecha_ini'] = $input['fecha_ini'];
                $data['fecha_fin'] = $input['fecha_fin'];

                $this->load->library('mpdf53/mpdf');
                $mpdf = new mPDF('utf-8', 'A4', 0, '', 5, 5, 5, 5, 5, 5);
                $html = $this->load->view('menu/reportes/productoVendido_list_pdf', $data, true);
                $mpdf->WriteHTML($html);
                $mpdf->Output();
                break;
            }
            case 'excel': {
                $params = json_decode($this->input->get('data'));
                $date_range = explode(' - ', $params->fecha);
                $input = array(
                    'local_id' => $params->local_id,
                    'marca_id' => $params->marca_id,
                    'grupo_id' => $params->grupo_id,
                    'familia_id' => $params->familia_id,
                    'linea_id' => $params->linea_id,
                    'producto_id' => $params->producto_id,
                    'fecha_ini' => date('Y-m-d 00:00:00', strtotime(str_replace("/", "-", $date_range[0]))),
                    'fecha_fin' => date('Y-m-d 23:59:59', strtotime(str_replace("/", "-", $date_range[1]))),
                    'tipo' => $this->input->post('tipo')
                );

                $data['lists'] = $this->reporte_model->getProductoVendido($input);

                $local = $this->db->get_where('local', array('int_local_id' => $input['local_id']))->row();
                $data['local_nombre'] = $local->local_nombre;
                $data['local_direccion'] = $local->direccion;

                $data['fecha_ini'] = $input['fecha_ini'];
                $data['fecha_fin'] = $input['fecha_fin'];

                echo $this->load->view('menu/reportes/productoVendido_list_excel', $data, true);
                break;
            }
            default: {
                if ($this->session->userdata('esSuper') == 1) {
                    $data['locales'] = $this->local_model->get_all();
                } else {
                    $usu = $this->session->userdata('nUsuCodigo');
                    $data['locales'] = $this->local_model->get_all_usu($usu);
                }
                $data['marcas'] = $this->db->get_where('marcas', array('estatus_marca' => 1))->result();
                $data['grupos'] = $this->db->get_where('grupos', array('estatus_grupo' => 1))->result();
                $data['familias'] = $this->db->get_where('familia', array('estatus_familia' => 1))->result();
                $data['lineas'] = $this->db->get_where('lineas', array('estatus_linea' => 1))->result();
                $data["productos"] = $this->producto_model->get_productos_list2();
                $data['barra_activa'] = $this->db->get_where('columnas', array('id_columna' => 36))->row();
                $dataCuerpo['cuerpo'] = $this->load->view('menu/reportes/productoVendido', $data, true);
                if ($this->input->is_ajax_request()) {
                    echo $dataCuerpo['cuerpo'];
                } else {
                    $this->load->view('menu/template', $dataCuerpo);
                }
                break;
            }
        }
    }

    function selectProducto()
    {
        $params['marca_id'] = $this->input->post('marca_id');
        $params['grupo_id'] = $this->input->post('grupo_id');
        $params['familia_id'] = $this->input->post('familia_id');
        $params['linea_id'] = $this->input->post('linea_id');
        $data['barra_activa'] = $this->db->get_where('columnas', array('id_columna' => 36))->row();
        $data["productos"] = $this->producto_model->get_productos_list2($params);
        $this->load->view('menu/reportes/selectProducto', $data);
    }

    function ventaSucursal($action = '')
    {
        switch ($action) {
            case 'filter': {
                $params['moneda_id'] = $this->input->post('moneda_id');
                $params['marca_id'] = $this->input->post('marca_id');
                $params['grupo_id'] = $this->input->post('grupo_id');
                $params['familia_id'] = $this->input->post('familia_id');
                $params['linea_id'] = $this->input->post('linea_id');
                $params['producto_id'] = $this->input->post('producto_id');
                $date_range = explode(" - ", $this->input->post('fecha'));
                $params['fecha_ini'] = date('Y-m-d 00:00:00', strtotime(str_replace("/", "-", $date_range[0])));
                $params['fecha_fin'] = date('Y-m-d 23:59:59', strtotime(str_replace("/", "-", $date_range[1])));
                $data['moneda'] = $this->db->get_where('moneda', array('id_moneda' => $params['moneda_id']))->row();
                $data['lists'] = $this->reporte_model->getVentaSucursal($params);
                if ($this->session->userdata('esSuper') == 1) {
                    $data['locales'] = $this->local_model->get_all();
                } else {
                    $usu = $this->session->userdata('nUsuCodigo');
                    $data['locales'] = $this->local_model->get_all_usu($usu);
                }
                $this->load->view('menu/reportes/ventaSucursal_list', $data);
                break;
            }
            case 'grafico': {
                $params['moneda_id'] = $this->input->post('moneda_id');
                $params['marca_id'] = $this->input->post('marca_id');
                $params['grupo_id'] = $this->input->post('grupo_id');
                $params['familia_id'] = $this->input->post('familia_id');
                $params['linea_id'] = $this->input->post('linea_id');
                $params['producto_id'] = $this->input->post('producto_id');
                $date_range = explode(" - ", $this->input->post('fecha'));
                $params['fecha_ini'] = date('Y-m-d 00:00:00', strtotime(str_replace("/", "-", $date_range[0])));
                $params['fecha_fin'] = date('Y-m-d 23:59:59', strtotime(str_replace("/", "-", $date_range[1])));
                $data['moneda'] = $this->db->get_where('moneda', array('id_moneda' => $params['moneda_id']))->row();
                $data['limit'] = $this->input->post('limit');
                $data['lists'] = $this->reporte_model->getVentaSucursal($params);
                if ($this->session->userdata('esSuper') == 1) {
                    $data['locales'] = $this->local_model->get_all();
                } else {
                    $usu = $this->session->userdata('nUsuCodigo');
                    $data['locales'] = $this->local_model->get_all_usu($usu);
                }
                echo json_encode($data);
                break;
            }
            case 'pdf': {
                $params = json_decode($this->input->get('data'));
                $date_range = explode(' - ', $params->fecha);
                $input = array(
                    'marca_id' => $params->marca_id,
                    'grupo_id' => $params->grupo_id,
                    'familia_id' => $params->familia_id,
                    'linea_id' => $params->linea_id,
                    'producto_id' => $params->producto_id,
                    'fecha_ini' => date('Y-m-d 00:00:00', strtotime(str_replace("/", "-", $date_range[0]))),
                    'fecha_fin' => date('Y-m-d 23:59:59', strtotime(str_replace("/", "-", $date_range[1]))),
                    'moneda_id' => $params->moneda_id
                );

                $data['lists'] = $this->reporte_model->getVentaSucursal($input);
                if ($this->session->userdata('esSuper') == 1) {
                    $data['locales'] = $this->local_model->get_all();
                } else {
                    $usu = $this->session->userdata('nUsuCodigo');
                    $data['locales'] = $this->local_model->get_all_usu($usu);
                }
                $data['moneda'] = $this->db->get_where('moneda', array('id_moneda' => $params->moneda_id))->row();
                $data['fecha_ini'] = $input['fecha_ini'];
                $data['fecha_fin'] = $input['fecha_fin'];

                $this->load->library('mpdf53/mpdf');
                $mpdf = new mPDF('utf-8', 'A4-L', 0, '', 5, 5, 5, 5, 5, 5);
                $html = $this->load->view('menu/reportes/ventaSucursal_list_pdf', $data, true);
                $mpdf->WriteHTML($html);
                $mpdf->Output();
                break;
            }
            case 'excel': {
                $params = json_decode($this->input->get('data'));
                $date_range = explode(' - ', $params->fecha);
                $input = array(
                    'marca_id' => $params->marca_id,
                    'grupo_id' => $params->grupo_id,
                    'familia_id' => $params->familia_id,
                    'linea_id' => $params->linea_id,
                    'producto_id' => $params->producto_id,
                    'fecha_ini' => date('Y-m-d 00:00:00', strtotime(str_replace("/", "-", $date_range[0]))),
                    'fecha_fin' => date('Y-m-d 23:59:59', strtotime(str_replace("/", "-", $date_range[1]))),
                    'moneda_id' => $params->moneda_id
                );

                $data['lists'] = $this->reporte_model->getVentaSucursal($input);
                if ($this->session->userdata('esSuper') == 1) {
                    $data['locales'] = $this->local_model->get_all();
                } else {
                    $usu = $this->session->userdata('nUsuCodigo');
                    $data['locales'] = $this->local_model->get_all_usu($usu);
                }
                $data['moneda'] = $this->db->get_where('moneda', array('id_moneda' => $params->moneda_id))->row();
                $data['fecha_ini'] = $input['fecha_ini'];
                $data['fecha_fin'] = $input['fecha_fin'];

                echo $this->load->view('menu/reportes/ventaSucursal_list_excel', $data, true);
                break;
            }
            default: {
                $data['marcas'] = $this->db->get_where('marcas', array('estatus_marca' => 1))->result();
                $data['grupos'] = $this->db->get_where('grupos', array('estatus_grupo' => 1))->result();
                $data['familias'] = $this->db->get_where('familia', array('estatus_familia' => 1))->result();
                $data['lineas'] = $this->db->get_where('lineas', array('estatus_linea' => 1))->result();
                $data["productos"] = $this->producto_model->get_productos_list();
                $data["monedas"] = $this->monedas_model->get_all();
                $data['barra_activa'] = $this->db->get_where('columnas', array('id_columna' => 36))->row();
                $dataCuerpo['cuerpo'] = $this->load->view('menu/reportes/ventaSucursal', $data, true);
                if ($this->input->is_ajax_request()) {
                    echo $dataCuerpo['cuerpo'];
                } else {
                    $this->load->view('menu/template', $dataCuerpo);
                }
                break;
            }
        }
    }

    function ventaEmpleado($action = '')
    {
        switch ($action) {
            case 'filter': {
                $params['local_id'] = $this->input->post('local_id');
                $params['moneda_id'] = $this->input->post('moneda_id');
                $params['marca_id'] = $this->input->post('marca_id');
                $params['grupo_id'] = $this->input->post('grupo_id');
                $params['familia_id'] = $this->input->post('familia_id');
                $params['linea_id'] = $this->input->post('linea_id');
                $params['producto_id'] = $this->input->post('producto_id');
                $params['tipo'] = $this->input->post('tipo');
                $date_range = explode(" - ", $this->input->post('fecha'));
                $params['fecha_ini'] = date('Y-m-d 00:00:00', strtotime(str_replace("/", "-", $date_range[0])));
                $params['fecha_fin'] = date('Y-m-d 23:59:59', strtotime(str_replace("/", "-", $date_range[1])));

                $data['moneda'] = $this->db->get_where('moneda', array('id_moneda' => $params['moneda_id']))->row();

                $data['lists'] = $this->reporte_model->getVentaEmpleado($params);

                $this->load->view('menu/reportes/ventaEmpleado_list', $data);
                break;
            }
            case 'grafico': {
                $params['local_id'] = $this->input->post('local_id');
                $params['moneda_id'] = $this->input->post('moneda_id');
                $params['marca_id'] = $this->input->post('marca_id');
                $params['grupo_id'] = $this->input->post('grupo_id');
                $params['familia_id'] = $this->input->post('familia_id');
                $params['linea_id'] = $this->input->post('linea_id');
                $params['producto_id'] = $this->input->post('producto_id');
                $params['tipo'] = $this->input->post('tipo');
                $date_range = explode(" - ", $this->input->post('fecha'));
                $params['fecha_ini'] = date('Y-m-d 00:00:00', strtotime(str_replace("/", "-", $date_range[0])));
                $params['fecha_fin'] = date('Y-m-d 23:59:59', strtotime(str_replace("/", "-", $date_range[1])));
                $data['moneda'] = $this->db->get_where('moneda', array('id_moneda' => $params['moneda_id']))->row();
                $params['limit'] = $this->input->post('limit');
                $data['lists'] = $this->reporte_model->getVentaEmpleado($params);
                echo json_encode($data);
                break;
            }
            case 'pdf': {
                $params = json_decode($this->input->get('data'));
                $date_range = explode(' - ', $params->fecha);
                $input = array(
                    'local_id' => $params->local_id,
                    'moneda_id' => $params->moneda_id,
                    'marca_id' => $params->marca_id,
                    'grupo_id' => $params->grupo_id,
                    'familia_id' => $params->familia_id,
                    'linea_id' => $params->linea_id,
                    'producto_id' => $params->producto_id,
                    'tipo' => $params->tipo,
                    'fecha_ini' => date('Y-m-d 00:00:00', strtotime(str_replace("/", "-", $date_range[0]))),
                    'fecha_fin' => date('Y-m-d 23:59:59', strtotime(str_replace("/", "-", $date_range[1])))
                );

                $data['lists'] = $this->reporte_model->getVentaEmpleado($input);
                $data['moneda'] = $this->db->get_where('moneda', array('id_moneda' => $params->moneda_id))->row();

                $local = $this->db->get_where('local', array('int_local_id' => $input['local_id']))->row();
                $data['local_nombre'] = $local->local_nombre;
                $data['local_direccion'] = $local->direccion;

                $data['fecha_ini'] = $input['fecha_ini'];
                $data['fecha_fin'] = $input['fecha_fin'];

                $this->load->library('mpdf53/mpdf');
                $mpdf = new mPDF('utf-8', 'A4', 0, '', 5, 5, 5, 5, 5, 5);
                $html = $this->load->view('menu/reportes/ventaEmpleado_list_pdf', $data, true);
                $mpdf->WriteHTML($html);
                $mpdf->Output();
                break;
                break;
            }
            case 'excel': {
                $params = json_decode($this->input->get('data'));
                $date_range = explode(' - ', $params->fecha);
                $input = array(
                    'local_id' => $params->local_id,
                    'moneda_id' => $params->moneda_id,
                    'marca_id' => $params->marca_id,
                    'grupo_id' => $params->grupo_id,
                    'familia_id' => $params->familia_id,
                    'linea_id' => $params->linea_id,
                    'producto_id' => $params->producto_id,
                    'tipo' => $params->tipo,
                    'fecha_ini' => date('Y-m-d 00:00:00', strtotime(str_replace("/", "-", $date_range[0]))),
                    'fecha_fin' => date('Y-m-d 23:59:59', strtotime(str_replace("/", "-", $date_range[1])))
                );

                $data['lists'] = $this->reporte_model->getVentaEmpleado($input);
                $data['moneda'] = $this->db->get_where('moneda', array('id_moneda' => $params->moneda_id))->row();

                $local = $this->db->get_where('local', array('int_local_id' => $input['local_id']))->row();
                $data['local_nombre'] = $local->local_nombre;
                $data['local_direccion'] = $local->direccion;

                $data['fecha_ini'] = $input['fecha_ini'];
                $data['fecha_fin'] = $input['fecha_fin'];

                echo $this->load->view('menu/reportes/ventaEmpleado_list_excel', $data, true);
                break;
            }
            default: {
                if ($this->session->userdata('esSuper') == 1) {
                    $data['locales'] = $this->local_model->get_all();
                } else {
                    $usu = $this->session->userdata('nUsuCodigo');
                    $data['locales'] = $this->local_model->get_all_usu($usu);
                }
                $data["monedas"] = $this->monedas_model->get_all();
                $data['marcas'] = $this->db->get_where('marcas', array('estatus_marca' => 1))->result();
                $data['grupos'] = $this->db->get_where('grupos', array('estatus_grupo' => 1))->result();
                $data['familias'] = $this->db->get_where('familia', array('estatus_familia' => 1))->result();
                $data['lineas'] = $this->db->get_where('lineas', array('estatus_linea' => 1))->result();
                $data["productos"] = $this->producto_model->get_productos_list();
                $data['barra_activa'] = $this->db->get_where('columnas', array('id_columna' => 36))->row();
                $dataCuerpo['cuerpo'] = $this->load->view('menu/reportes/ventaEmpleado', $data, true);
                if ($this->input->is_ajax_request()) {
                    echo $dataCuerpo['cuerpo'];
                } else {
                    $this->load->view('menu/template', $dataCuerpo);
                }
                break;
            }
        }
    }

    function margenUtilidad($action = '')
    {
        switch ($action) {
            case 'filter': {
                $params['local_id'] = $this->input->post('local_id');
                $params['moneda_id'] = $this->input->post('moneda_id');
                $params['marca_id'] = $this->input->post('marca_id');
                $params['grupo_id'] = $this->input->post('grupo_id');
                $params['familia_id'] = $this->input->post('familia_id');
                $params['linea_id'] = $this->input->post('linea_id');
                $params['producto_id'] = $this->input->post('producto_id');
                $date_range = explode(" - ", $this->input->post('fecha'));
                $params['fecha_ini'] = date('Y-m-d 00:00:00', strtotime(str_replace("/", "-", $date_range[0])));
                $params['fecha_fin'] = date('Y-m-d 23:59:59', strtotime(str_replace("/", "-", $date_range[1])));

                $data['lists'] = $this->reporte_model->getMargenUtilidad($params);

                $this->load->view('menu/reportes/margenUtilidad_list', $data);
                break;
            }
            case 'pdf': {
                $params = json_decode($this->input->get('data'));
                $date_range = explode(' - ', $params->fecha);
                $input = array(
                    'local_id' => $params->local_id,
                    'moneda_id' => $params->moneda_id,
                    'marca_id' => $params->marca_id,
                    'grupo_id' => $params->grupo_id,
                    'familia_id' => $params->familia_id,
                    'linea_id' => $params->linea_id,
                    'producto_id' => $params->producto_id,
                    'fecha_ini' => date('Y-m-d 00:00:00', strtotime(str_replace("/", "-", $date_range[0]))),
                    'fecha_fin' => date('Y-m-d 23:59:59', strtotime(str_replace("/", "-", $date_range[1])))
                );

                $data['lists'] = $this->reporte_model->getMargenUtilidad($input);

                $local = $this->db->get_where('local', array('int_local_id' => $input['local_id']))->row();
                $data['local_nombre'] = $local->local_nombre;
                $data['local_direccion'] = $local->direccion;

                $data['fecha_ini'] = $input['fecha_ini'];
                $data['fecha_fin'] = $input['fecha_fin'];

                $this->load->library('mpdf53/mpdf');
                $mpdf = new mPDF('utf-8', 'A4-L', 0, '', 5, 5, 5, 5, 5, 5);
                $html = $this->load->view('menu/reportes/margenUtilidad_list_pdf', $data, true);
                $mpdf->WriteHTML($html);
                $mpdf->Output();
                break;
                break;
            }
            case 'excel': {
                $params = json_decode($this->input->get('data'));
                $date_range = explode(' - ', $params->fecha);
                $input = array(
                    'local_id' => $params->local_id,
                    'moneda_id' => $params->moneda_id,
                    'marca_id' => $params->marca_id,
                    'grupo_id' => $params->grupo_id,
                    'familia_id' => $params->familia_id,
                    'linea_id' => $params->linea_id,
                    'producto_id' => $params->producto_id,
                    'fecha_ini' => date('Y-m-d 00:00:00', strtotime(str_replace("/", "-", $date_range[0]))),
                    'fecha_fin' => date('Y-m-d 23:59:59', strtotime(str_replace("/", "-", $date_range[1])))
                );

                $data['lists'] = $this->reporte_model->getMargenUtilidad($input);

                $local = $this->db->get_where('local', array('int_local_id' => $input['local_id']))->row();
                $data['local_nombre'] = $local->local_nombre;
                $data['local_direccion'] = $local->direccion;

                $data['fecha_ini'] = $input['fecha_ini'];
                $data['fecha_fin'] = $input['fecha_fin'];

                echo $this->load->view('menu/reportes/margenUtilidad_list_excel', $data, true);
                break;
            }
            default: {
                if ($this->session->userdata('esSuper') == 1) {
                    $data['locales'] = $this->local_model->get_all();
                } else {
                    $usu = $this->session->userdata('nUsuCodigo');
                    $data['locales'] = $this->local_model->get_all_usu($usu);
                }
                $data['monedas'] = $this->db->get_where('moneda', array('status_moneda' => 1))->result();
                $data['marcas'] = $this->db->get_where('marcas', array('estatus_marca' => 1))->result();
                $data['grupos'] = $this->db->get_where('grupos', array('estatus_grupo' => 1))->result();
                $data['familias'] = $this->db->get_where('familia', array('estatus_familia' => 1))->result();
                $data['lineas'] = $this->db->get_where('lineas', array('estatus_linea' => 1))->result();
                $data["productos"] = $this->producto_model->get_productos_list();
                $data['barra_activa'] = $this->db->get_where('columnas', array('id_columna' => 36))->row();
                $dataCuerpo['cuerpo'] = $this->load->view('menu/reportes/margenUtilidad', $data, true);
                if ($this->input->is_ajax_request()) {
                    echo $dataCuerpo['cuerpo'];
                } else {
                    $this->load->view('menu/template', $dataCuerpo);
                }
                break;
            }
        }
    }

    function stockVentas($action = '')
    {
        switch ($action) {
            case 'filter': {
                $params['tipo_periodo'] = $this->input->post('tipo_periodo');
                $params['marca_id'] = $this->input->post('marca_id');
                $params['grupo_id'] = $this->input->post('grupo_id');
                $params['familia_id'] = $this->input->post('familia_id');
                $params['linea_id'] = $this->input->post('linea_id');
                $params['producto_id'] = $this->input->post('producto_id');
                $params['local_id'] = json_decode($this->input->post('local_id'));
                $params['rangos'] = json_decode($this->input->post('rangos'));
                $params['tipo'] = $this->input->post('tipo');

                $this->db->select('local_nombre');
                $this->db->where_in('int_local_id', $params['local_id']);
                $sqlLocal = $this->db->get('local');
                $data['locale'] = $sqlLocal->result_array();

                $this->db->select('int_local_id');
                $this->db->where_in('int_local_id', $params['local_id']);
                $sqlLocal = $this->db->get('local');
                $data['localId'] = $sqlLocal->result_array();
                $data['tipo'] = $params['tipo'];
                $data['periodo'] = $params['rangos'];

                $data['lists'] = $this->reporte_model->getStockVentas($params);
                $this->load->view('menu/reportes/stockVentas_list', $data);
                break;
            }
            case 'pdf': {
                $params = json_decode($this->input->get('data'));
                $input = array(
                    'marca_id' => $params->marca_id,
                    'grupo_id' => $params->grupo_id,
                    'familia_id' => $params->familia_id,
                    'linea_id' => $params->linea_id,
                    'producto_id' => $params->producto_id,
                    'tipo_periodo' => $params->tipo_periodo,
                    'local_id' => json_decode($params->local_id),
                    'rangos' => json_decode($params->rangos),
                    'tipo' => $params->tipo,
                );

                $data['lists'] = $this->reporte_model->getStockVentas($input);

                $rango = json_decode($params->rangos);

                $ArrayFechaI =explode('/', $rango[0]);
                $ArrayFechaF =explode('/', $rango[count($rango)-1]);

                if($params->tipo_periodo=='1'){ //por dia
                    $fechaI = $ArrayFechaI[2] ."-".$ArrayFechaI[1] ."-".$ArrayFechaI[0];
                    $fecha_ini = date('Y-m-d 00:00:00', strtotime($fechaI));                
                    $fechaF = $ArrayFechaF[2] ."-".$ArrayFechaF[1] ."-".$ArrayFechaF[0];
                    $fecha_fin = date('Y-m-d 23:59:59', strtotime($fechaF));
                }elseif($params->tipo_periodo=='2') { //por mes
                    $fechaI = $ArrayFechaI[1] ."-".$ArrayFechaI[0] ."-01";
                    $fecha_ini = date('Y-m-d 00:00:00', strtotime($fechaI));
                    $fechaF = $ArrayFechaF[1] ."-".$ArrayFechaF[0];
                    $aux = date('Y-m-d 00:00:00', strtotime("{$fechaF} + 1 month"));
                    $fecha_fin = date('Y-m-d 23:59:59', strtotime("{$aux} - 1 day"));
                }elseif($params->tipo_periodo=='3') { //por año
                    $fechaI = $ArrayFechaI[0] ."-01-01";
                    $fecha_ini = date('Y-m-d 00:00:00', strtotime($fechaI));

                    $fechaF = $ArrayFechaF[0] ."-12";
                    $aux = date('Y-m-d 00:00:00', strtotime("{$fechaF} + 1 month"));
                    $fecha_fin = date('Y-m-d 23:59:59', strtotime("{$aux} - 1 day"));
                }

                $data['fecha_ini'] = $fecha_ini;
                $data['fecha_fin'] = $fecha_fin;

                $this->db->select('local_nombre');
                $this->db->where_in('int_local_id', json_decode($params->local_id));
                $sqlLocal = $this->db->get('local');
                $data['locale'] = $sqlLocal->result_array();

                $this->db->select('int_local_id');
                $this->db->where_in('int_local_id', json_decode($params->local_id));
                $sqlLocal = $this->db->get('local');
                $data['localId'] = $sqlLocal->result_array();
                $data['tipo'] = $params->tipo;
                $data['periodo'] = json_decode($params->rangos);

                $this->load->library('mpdf53/mpdf');
                $mpdf = new mPDF('utf-8', 'A4-L', 0, '', 5, 5, 5, 5, 5, 5);
                $html = $this->load->view('menu/reportes/stockVentas_list_pdf', $data, true);
                $mpdf->WriteHTML($html);
                $mpdf->Output();
                break;
            }
            case 'excel': {
                $params = json_decode($this->input->get('data'));
                $input = array(
                    'marca_id' => $params->marca_id,
                    'grupo_id' => $params->grupo_id,
                    'familia_id' => $params->familia_id,
                    'linea_id' => $params->linea_id,
                    'producto_id' => $params->producto_id,
                    'tipo_periodo' => $params->tipo_periodo,
                    'local_id' => json_decode($params->local_id),
                    'rangos' => json_decode($params->rangos),
                    'tipo' => $params->tipo,
                );

                $data['lists'] = $this->reporte_model->getStockVentas($input);

                $rango = json_decode($params->rangos);
                $ArrayFechaI =explode('/', $rango[0]);
                $ArrayFechaF =explode('/', $rango[count($rango)-1]);

                if($params->tipo_periodo=='1'){ //por dia
                    $fechaI = $ArrayFechaI[2] ."-".$ArrayFechaI[1] ."-".$ArrayFechaI[0];
                    $fecha_ini = date('Y-m-d 00:00:00', strtotime($fechaI));                
                    $fechaF = $ArrayFechaF[2] ."-".$ArrayFechaF[1] ."-".$ArrayFechaF[0];
                    $fecha_fin = date('Y-m-d 23:59:59', strtotime($fechaF));
                }elseif($params->tipo_periodo=='2') { //por mes
                    $fechaI = $ArrayFechaI[1] ."-".$ArrayFechaI[0] ."-01";
                    $fecha_ini = date('Y-m-d 00:00:00', strtotime($fechaI));
                    $fechaF = $ArrayFechaF[1] ."-".$ArrayFechaF[0];
                    $aux = date('Y-m-d 00:00:00', strtotime("{$fechaF} + 1 month"));
                    $fecha_fin = date('Y-m-d 23:59:59', strtotime("{$aux} - 1 day"));
                }elseif($params->tipo_periodo=='3') { //por año
                    $fechaI = $ArrayFechaI[0] ."-01-01";
                    $fecha_ini = date('Y-m-d 00:00:00', strtotime($fechaI));

                    $fechaF = $ArrayFechaF[0] ."-12";
                    $aux = date('Y-m-d 00:00:00', strtotime("{$fechaF} + 1 month"));
                    $fecha_fin = date('Y-m-d 23:59:59', strtotime("{$aux} - 1 day"));
                }
                $data['fecha_ini'] = $fecha_ini;
                $data['fecha_fin'] = $fecha_fin;                               

                $this->db->select('local_nombre');
                $this->db->where_in('int_local_id', json_decode($params->local_id));
                $sqlLocal = $this->db->get('local');
                $data['locale'] = $sqlLocal->result_array();

                $this->db->select('int_local_id');
                $this->db->where_in('int_local_id', json_decode($params->local_id));
                $sqlLocal = $this->db->get('local');
                $data['localId'] = $sqlLocal->result_array();
                $data['tipo'] = $params->tipo;
                $data['periodo'] = json_decode($params->rangos);
                echo $this->load->view('menu/reportes/stockVentas_list_excel', $data, true);
                break;
            }
            default: {
                if ($this->session->userdata('esSuper') == 1) {
                    $data['locales'] = $this->local_model->get_all();
                } else {
                    $usu = $this->session->userdata('nUsuCodigo');
                    $data['locales'] = $this->local_model->get_all_usu($usu);
                }
                $data['marcas'] = $this->db->get_where('marcas', array('estatus_marca' => 1))->result();
                $data['grupos'] = $this->db->get_where('grupos', array('estatus_grupo' => 1))->result();
                $data['familias'] = $this->db->get_where('familia', array('estatus_familia' => 1))->result();
                $data['lineas'] = $this->db->get_where('lineas', array('estatus_linea' => 1))->result();
                $data["productos"] = $this->producto_model->get_productos_list();
                $data['barra_activa'] = $this->db->get_where('columnas', array('id_columna' => 36))->row();
                $dataCuerpo['cuerpo'] = $this->load->view('menu/reportes/stockVentas', $data, true);
                if ($this->input->is_ajax_request()) {
                    echo $dataCuerpo['cuerpo'];
                } else {
                    $this->load->view('menu/template', $dataCuerpo);
                }
                break;
            }
        }
    }

    function hojaColecta($action = '')
    {
        switch ($action) {
            case 'filter': {
                $params['local_id'] = $this->input->post('local_id');
                $params['marca_id'] = $this->input->post('marca_id');
                $params['grupo_id'] = $this->input->post('grupo_id');
                $params['familia_id'] = $this->input->post('familia_id');
                $params['linea_id'] = $this->input->post('linea_id');
                $params['producto_id'] = $this->input->post('producto_id');
                $date_range = explode(" - ", $this->input->post('fecha'));
                $params['fecha_ini'] = date('Y-m-d 00:00:00', strtotime(str_replace("/", "-", $date_range[0])));
                $params['fecha_fin'] = date('Y-m-d 23:59:59', strtotime(str_replace("/", "-", $date_range[1])));
                $params['operador_id'] = $this->input->post('operador_id');
                $params['usuario_id'] = $this->input->post('usuario_id');
                $data['lists'] = $this->reporte_model->getHojaColecta($params);
                $data['totalesCon'] = $this->reporte_model->getSumMedioPago($params, 1); //contado
                $data['totalesCre'] = $this->reporte_model->getSumMedioPago($params, 2); //credito
                $this->load->view('menu/reportes/hojaColecta_list', $data);
                break;
            }
            case 'pdf': {
                $params = json_decode($this->input->get('data'));
                $date_range = explode(' - ', $params->fecha);
                $input = array(
                    'local_id' => $params->local_id,
                    'marca_id' => $params->marca_id,
                    'grupo_id' => $params->grupo_id,
                    'familia_id' => $params->familia_id,
                    'linea_id' => $params->linea_id,
                    'producto_id' => $params->producto_id,
                    'fecha_ini' => date('Y-m-d 00:00:00', strtotime(str_replace("/", "-", $date_range[0]))),
                    'fecha_fin' => date('Y-m-d 23:59:59', strtotime(str_replace("/", "-", $date_range[1]))),
                    'operador_id' => $params->operador_id,
                    'usuario_id' => $params->usuario_id
                );

                $data['lists'] = $this->reporte_model->getHojaColecta($input);
                $data['totalesCon'] = $this->reporte_model->getSumMedioPago($input, 1); //contado
                //$data['totalesCre'] = $this->reporte_model->getSumMedioPago($params, 2); //credito
                $local = $this->db->get_where('local', array('int_local_id' => $input['local_id']))->row();
                $data['local_nombre'] = !empty($local->local_nombre)? $local->local_nombre: 'TODOS';
                $data['local_direccion'] = !empty($local->direccion)? $local->direccion: 'TODOS';

                $data['fecha_ini'] = $input['fecha_ini'];
                $data['fecha_fin'] = $input['fecha_fin'];

                $this->load->library('mpdf53/mpdf');
                $mpdf = new mPDF('utf-8', 'A4-L', 0, '', 5, 5, 5, 5, 5, 5);
                $html = $this->load->view('menu/reportes/hojaColecta_list_pdf', $data, true);
                $mpdf->WriteHTML($html);
                $mpdf->Output();
                break;
            }
            case 'excel': {
                $params = json_decode($this->input->get('data'));
                $date_range = explode(' - ', $params->fecha);
                $input = array(
                    'local_id' => $params->local_id,
                    'marca_id' => $params->marca_id,
                    'grupo_id' => $params->grupo_id,
                    'familia_id' => $params->familia_id,
                    'linea_id' => $params->linea_id,
                    'producto_id' => $params->producto_id,
                    'fecha_ini' => date('Y-m-d 00:00:00', strtotime(str_replace("/", "-", $date_range[0]))),
                    'fecha_fin' => date('Y-m-d 23:59:59', strtotime(str_replace("/", "-", $date_range[1]))),
                    'operador_id' => $params->operador_id,
                    'usuario_id' => $params->usuario_id
                );

                $data['lists'] = $this->reporte_model->getHojaColecta($input);
                $data['totalesCon'] = $this->reporte_model->getSumMedioPago($input, 1); //contado
                //$data['totalesCre'] = $this->reporte_model->getSumMedioPago($params, 2); //credito
                $local = $this->db->get_where('local', array('int_local_id' => $input['local_id']))->row();
                $data['local_nombre'] = !empty($local->local_nombre)? $local->local_nombre: 'TODOS';
                $data['local_direccion'] = !empty($local->direccion)? $local->direccion: 'TODOS';

                $data['fecha_ini'] = $input['fecha_ini'];
                $data['fecha_fin'] = $input['fecha_fin'];

                echo $this->load->view('menu/reportes/hojaColecta_list_excel', $data, true);
                break;
            }
            default: {
                $usu = $this->session->userdata('nUsuCodigo');
                if ($this->session->userdata('esSuper') == 1) {
                    $data['locales'] = $this->local_model->get_all();
                } else {
                    $data['locales'] = $this->local_model->get_all_usu($usu);
                }
                $data['marcas'] = $this->db->get_where('marcas', array('estatus_marca' => 1))->result();
                $data['grupos'] = $this->db->get_where('grupos', array('estatus_grupo' => 1))->result();
                $data['familias'] = $this->db->get_where('familia', array('estatus_familia' => 1))->result();
                $data['lineas'] = $this->db->get_where('lineas', array('estatus_linea' => 1))->result();
                $data["productos"] = $this->producto_model->get_productos_list2();
                $data['barra_activa'] = $this->db->get_where('columnas', array('id_columna' => 36))->row();
                $data['operadore'] = $this->diccionario_termino_model->get_all_operador();
                if ($this->session->userdata('grupo') == 8) { //perfil de vendedor
                    $data['usuarios'] = $this->usuario_model->buscar_id($usu);
                }else{
                    $data['usuarios'] = $this->usuario_model->select_all_user();
                }
                $dataCuerpo['cuerpo'] = $this->load->view('menu/reportes/hojaColecta', $data, true);
                if ($this->input->is_ajax_request()) {
                    echo $dataCuerpo['cuerpo'];
                } else {
                    $this->load->view('menu/template', $dataCuerpo);
                }
                break;
            }
        }        
    }

    function recargaDia($action = '')
    {
        switch ($action) {
            case 'filter': {
                $params['local_id'] = $this->input->post('local_id');
                if(!empty($this->input->post('fecha'))){
                    $date_range = explode(" - ", $this->input->post('fecha'));
                    $params['fecha_ini'] = date('Y-m-d 00:00:00', strtotime(str_replace("/", "-", $date_range[0])));
                    $params['fecha_fin'] = date('Y-m-d 23:59:59', strtotime(str_replace("/", "-", $date_range[1])));
                }
                $params['estado_pago'] = $this->input->post('estado_pago');
                $params['poblado_id'] = $this->input->post('poblado_id');
                $data['estado_pago'] = $params['estado_pago'];
                $params['usuario_id'] = $this->input->post('usuario_id');
                $data['lists'] = $this->reporte_model->getRecargaDia($params);

                $this->load->view('menu/reportes/recargaDia_list', $data);
                break;
            }
            case 'pdf': {
                $params = json_decode($this->input->get('data'));
                if(!empty($params->fecha)){
                    $date_range = explode(' - ', $params->fecha);
                    $input = array(
                        'local_id' => $params->local_id,
                        'fecha_ini' => date('Y-m-d 00:00:00', strtotime(str_replace("/", "-", $date_range[0]))),
                        'fecha_fin' => date('Y-m-d 23:59:59', strtotime(str_replace("/", "-", $date_range[1]))),
                        'estado_pago' => $params->estado_pago,
                        'poblado_id' => $params->poblado_id,
                        'usuario_id' => $params->usuario_id
                    );
                }else{
                    $input = array(
                        'local_id' => $params->local_id,
                        'estado_pago' => $params->estado_pago,
                        'poblado_id' => $params->poblado_id,
                        'usuario_id' => $params->usuario_id
                    );                    
                }

                $data['lists'] = $this->reporte_model->getRecargaDia($input);

                $local = $this->db->get_where('local', array('int_local_id' => $input['local_id']))->row();
                $data['local_nombre'] = !empty($local->local_nombre)? $local->local_nombre: 'TODOS';
                $data['local_direccion'] = !empty($local->direccion)? $local->direccion: 'TODOS';

                $data['fecha_ini'] = $input['fecha_ini'];
                $data['fecha_fin'] = $input['fecha_fin'];
                $data['condicion_pago'] = $input['condicion_pago'];
                $data['estado_pago'] = $input['estado_pago'];
                $this->load->library('mpdf53/mpdf');
                $mpdf = new mPDF('utf-8', 'A4-L', 0, '', 5, 5, 5, 5, 5, 5);
                $html = $this->load->view('menu/reportes/recargaDia_list_pdf', $data, true);
                $mpdf->WriteHTML($html);
                $mpdf->Output();
                break;
            }
            case 'excel': {
                $params = json_decode($this->input->get('data'));
                if(!empty($params->fecha)){
                    $date_range = explode(' - ', $params->fecha);
                    $input = array(
                        'local_id' => $params->local_id,
                        'fecha_ini' => date('Y-m-d 00:00:00', strtotime(str_replace("/", "-", $date_range[0]))),
                        'fecha_fin' => date('Y-m-d 23:59:59', strtotime(str_replace("/", "-", $date_range[1]))),
                        'estado_pago' => $params->estado_pago,
                        'poblado_id' => $params->poblado_id,
                        'usuario_id' => $params->usuario_id
                    );
                    $data['fecha_ini'] = $input['fecha_ini'];
                    $data['fecha_fin'] = $input['fecha_fin'];
                }else{
                    $input = array(
                        'local_id' => $params->local_id,
                        'estado_pago' => $params->estado_pago,
                        'poblado_id' => $params->poblado_id,
                        'usuario_id' => $params->usuario_id
                    );
                }

                $data['lists'] = $this->reporte_model->getRecargaDia($input);

                $local = $this->db->get_where('local', array('int_local_id' => $input['local_id']))->row();
                $data['local_nombre'] = !empty($local->local_nombre)? $local->local_nombre: 'TODOS';
                $data['local_direccion'] = !empty($local->direccion)? $local->direccion: 'TODOS';
                $data['estado_pago'] = $input['estado_pago'];
                echo $this->load->view('menu/reportes/recargaDia_list_excel', $data, true);
                break;
            }
            default: {
                if ($this->session->userdata('esSuper') == 1) {
                    $data['locales'] = $this->local_model->get_all();
                } else {
                    $usu = $this->session->userdata('nUsuCodigo');
                    $data['locales'] = $this->local_model->get_all_usu($usu);
                }
                $data['condiciones_pagos'] = $this->db->get_where('condiciones_pago', array('status_condiciones' => 1))->result();
                $data['poblados'] = $this->clientes_grupos_model->get_all();
                if ($this->session->userdata('grupo') == 8) { //perfil de vendedor
                    $data['usuarios'] = $this->usuario_model->buscar_id($usu);
                }else{
                    $data['usuarios'] = $this->usuario_model->select_all_user();
                }
                $dataCuerpo['cuerpo'] = $this->load->view('menu/reportes/recargaDia', $data, true);
                if ($this->input->is_ajax_request()) {
                    echo $dataCuerpo['cuerpo'];
                } else {
                    $this->load->view('menu/template', $dataCuerpo);
                }
                break;
            }
        }        
    }
    function recargaCobranza($action = '')
    {
        switch ($action) {
            case 'filter': {
                $params['local_id'] = $this->input->post('local_id');
                if(!empty($this->input->post('fecha'))){
                    $date_range = explode(" - ", $this->input->post('fecha'));
                    $params['fecha_ini'] = date('Y-m-d 00:00:00', strtotime(str_replace("/", "-", $date_range[0])));
                    $params['fecha_fin'] = date('Y-m-d 23:59:59', strtotime(str_replace("/", "-", $date_range[1])));
                }
                $params['estado_pago'] = $this->input->post('estado_pago');
                $params['poblado_id'] = $this->input->post('poblado_id');
                $data['estado_pago'] = $params['estado_pago'];
                $params['usuario_id'] = $this->input->post('usuario_id');
                $data['lists'] = $this->reporte_model->getRecargaCobranza($params);

                $this->load->view('menu/reportes/recargaCobranza_list', $data);
                break;
            }
            case 'pdf': {
                $params = json_decode($this->input->get('data'));
                if(!empty($params->fecha)){
                    $date_range = explode(' - ', $params->fecha);
                    $input = array(
                        'local_id' => $params->local_id,
                        'fecha_ini' => date('Y-m-d 00:00:00', strtotime(str_replace("/", "-", $date_range[0]))),
                        'fecha_fin' => date('Y-m-d 23:59:59', strtotime(str_replace("/", "-", $date_range[1]))),
                        'estado_pago' => $params->estado_pago,
                        'poblado_id' => $params->poblado_id,
                        'usuario_id' => $params->usuario_id
                    );
                }else{
                    $input = array(
                        'local_id' => $params->local_id,
                        'estado_pago' => $params->estado_pago,
                        'poblado_id' => $params->poblado_id,
                        'usuario_id' => $params->usuario_id
                    );                    
                }

                $data['lists'] = $this->reporte_model->getRecargaCobranza($input);

                $local = $this->db->get_where('local', array('int_local_id' => $input['local_id']))->row();
                $data['local_nombre'] = !empty($local->local_nombre)? $local->local_nombre: 'TODOS';
                $data['local_direccion'] = !empty($local->direccion)? $local->direccion: 'TODOS';

                $data['fecha_ini'] = $input['fecha_ini'];
                $data['fecha_fin'] = $input['fecha_fin'];
                $data['condicion_pago'] = $input['condicion_pago'];
                $data['estado_pago'] = $input['estado_pago'];
                $this->load->library('mpdf53/mpdf');
                $mpdf = new mPDF('utf-8', 'A4-L', 0, '', 5, 5, 5, 5, 5, 5);
                $html = $this->load->view('menu/reportes/recargaCobranza_list_pdf', $data, true);
                $mpdf->WriteHTML($html);
                $mpdf->Output();
                break;
            }
            case 'excel': {
                $params = json_decode($this->input->get('data'));
                if(!empty($params->fecha)){
                    $date_range = explode(' - ', $params->fecha);
                    $input = array(
                        'local_id' => $params->local_id,
                        'fecha_ini' => date('Y-m-d 00:00:00', strtotime(str_replace("/", "-", $date_range[0]))),
                        'fecha_fin' => date('Y-m-d 23:59:59', strtotime(str_replace("/", "-", $date_range[1]))),
                        'estado_pago' => $params->estado_pago,
                        'poblado_id' => $params->poblado_id,
                        'usuario_id' => $params->usuario_id
                    );
                    $data['fecha_ini'] = $input['fecha_ini'];
                    $data['fecha_fin'] = $input['fecha_fin'];
                }else{
                    $input = array(
                        'local_id' => $params->local_id,
                        'estado_pago' => $params->estado_pago,
                        'poblado_id' => $params->poblado_id,
                        'usuario_id' => $params->usuario_id
                    );
                }

                $data['lists'] = $this->reporte_model->getRecargaCobranza($input);

                $local = $this->db->get_where('local', array('int_local_id' => $input['local_id']))->row();
                $data['local_nombre'] = !empty($local->local_nombre)? $local->local_nombre: 'TODOS';
                $data['local_direccion'] = !empty($local->direccion)? $local->direccion: 'TODOS';
                $data['estado_pago'] = $input['estado_pago'];
                echo $this->load->view('menu/reportes/recargaCobranza_list_excel', $data, true);
                break;
            }
            default: {
                if ($this->session->userdata('esSuper') == 1) {
                    $data['locales'] = $this->local_model->get_all();
                } else {
                    $usu = $this->session->userdata('nUsuCodigo');
                    $data['locales'] = $this->local_model->get_all_usu($usu);
                }
                $data['condiciones_pagos'] = $this->db->get_where('condiciones_pago', array('status_condiciones' => 1))->result();
                $data['poblados'] = $this->clientes_grupos_model->get_all();
                if ($this->session->userdata('grupo') == 8) { //perfil de vendedor
                    $data['usuarios'] = $this->usuario_model->buscar_id($usu);
                }else{
                    $data['usuarios'] = $this->usuario_model->select_all_user();
                }
                $dataCuerpo['cuerpo'] = $this->load->view('menu/reportes/recargaCobranza', $data, true);
                if ($this->input->is_ajax_request()) {
                    echo $dataCuerpo['cuerpo'];
                } else {
                    $this->load->view('menu/template', $dataCuerpo);
                }
                break;
            }
        }
    }
    function recargaCuentasC($action = '')
    {
        switch ($action) {
            case 'filter': {
                $params['local_id'] = $this->input->post('local_id');
                if(!empty($this->input->post('fecha'))){
                    $date_range = explode(" - ", $this->input->post('fecha'));
                    $params['fecha_ini'] = date('Y-m-d 00:00:00', strtotime(str_replace("/", "-", $date_range[0])));
                    $params['fecha_fin'] = date('Y-m-d 23:59:59', strtotime(str_replace("/", "-", $date_range[1])));
                }
                $params['poblado_id'] = $this->input->post('poblado_id');
                $params['usuario_id'] = $this->input->post('usuario_id');
                $data['lists'] = $this->reporte_model->getRecargaCuentasC($params);

                $this->load->view('menu/reportes/recargaCuentasC_list', $data);
                break;
            }
            case 'pdf': {
                $params = json_decode($this->input->get('data'));
                if(!empty($params->fecha)){
                    $date_range = explode(' - ', $params->fecha);
                    $input = array(
                        'local_id' => $params->local_id,
                        'fecha_ini' => date('Y-m-d 00:00:00', strtotime(str_replace("/", "-", $date_range[0]))),
                        'fecha_fin' => date('Y-m-d 23:59:59', strtotime(str_replace("/", "-", $date_range[1]))),
                        'poblado_id' => $params->poblado_id,
                        'usuario_id' => $params->usuario_id
                    );
                }else{
                    $input = array(
                        'local_id' => $params->local_id,
                        'poblado_id' => $params->poblado_id,
                        'usuario_id' => $params->usuario_id
                    );                    
                }

                $data['lists'] = $this->reporte_model->getRecargaCuentasC($input);

                $local = $this->db->get_where('local', array('int_local_id' => $input['local_id']))->row();
                $data['local_nombre'] = !empty($local->local_nombre)? $local->local_nombre: 'TODOS';
                $data['local_direccion'] = !empty($local->direccion)? $local->direccion: 'TODOS';

                $data['fecha_ini'] = $input['fecha_ini'];
                $data['fecha_fin'] = $input['fecha_fin'];
                $data['condicion_pago'] = $input['condicion_pago'];
                $this->load->library('mpdf53/mpdf');
                $mpdf = new mPDF('utf-8', 'A4-L', 0, '', 5, 5, 5, 5, 5, 5);
                $html = $this->load->view('menu/reportes/recargaCuentasC_list_pdf', $data, true);
                $mpdf->WriteHTML($html);
                $mpdf->Output();
                break;
            }
            case 'excel': {
                $params = json_decode($this->input->get('data'));
                if(!empty($params->fecha)){
                    $date_range = explode(' - ', $params->fecha);
                    $input = array(
                        'local_id' => $params->local_id,
                        'fecha_ini' => date('Y-m-d 00:00:00', strtotime(str_replace("/", "-", $date_range[0]))),
                        'fecha_fin' => date('Y-m-d 23:59:59', strtotime(str_replace("/", "-", $date_range[1]))),
                        'poblado_id' => $params->poblado_id,
                        'usuario_id' => $params->usuario_id
                    );
                    $data['fecha_ini'] = $input['fecha_ini'];
                    $data['fecha_fin'] = $input['fecha_fin'];
                }else{
                    $input = array(
                        'local_id' => $params->local_id,
                        'poblado_id' => $params->poblado_id,
                        'usuario_id' => $params->usuario_id
                    );
                }

                $data['lists'] = $this->reporte_model->getRecargaCuentasC($input);

                $local = $this->db->get_where('local', array('int_local_id' => $input['local_id']))->row();
                $data['local_nombre'] = !empty($local->local_nombre)? $local->local_nombre: 'TODOS';
                $data['local_direccion'] = !empty($local->direccion)? $local->direccion: 'TODOS';
                echo $this->load->view('menu/reportes/recargaCuentasC_list_excel', $data, true);
                break;
            }
            default: {
                if ($this->session->userdata('esSuper') == 1) {
                    $data['locales'] = $this->local_model->get_all();
                } else {
                    $usu = $this->session->userdata('nUsuCodigo');
                    $data['locales'] = $this->local_model->get_all_usu($usu);
                }
                $data['condiciones_pagos'] = $this->db->get_where('condiciones_pago', array('status_condiciones' => 1))->result();
                $data['poblados'] = $this->clientes_grupos_model->get_all();
                if ($this->session->userdata('grupo') == 8) { //perfil de vendedor
                    $data['usuarios'] = $this->usuario_model->buscar_id($usu);
                }else{
                    $data['usuarios'] = $this->usuario_model->select_all_user();
                }
                $dataCuerpo['cuerpo'] = $this->load->view('menu/reportes/recargaCuentasC', $data, true);
                if ($this->input->is_ajax_request()) {
                    echo $dataCuerpo['cuerpo'];
                } else {
                    $this->load->view('menu/template', $dataCuerpo);
                }
                break;
            }
        }        
    }
}