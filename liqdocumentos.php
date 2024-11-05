<?php
include "conexion.php";

try {
    $sql = "SELECT co_tipo, de_tipo_liquidacion FROM TIPO_LIQUIDACION";
    $stmt = $conn->query($sql);
    $tipos_liquidacion = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sql1="select co_tip_maestro, de_tip_maestro from TIPO_MAESTRO";
    $stmt1 = $conn->query($sql1);
    $tipos_maestro = $stmt1->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error al ejecutar la consulta: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Liquidaciones</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@10">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <style>.form-group-hidden{display:none}</style>
</head>
<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <a class="navbar-brand ps-3" href="index.html">AC Tours</a>
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>
        <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-user fa-fw"></i></a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item" href="#!">Salir</a></li>
                </ul>
            </li>
        </ul>
    </nav>
    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                    <div class="nav">
                        <div class="sb-sidenav-menu-heading">Utilitarios</div>
                        <a class="nav-link" href="index.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                            Liquidaciones
                        </a>
                        <a class="nav-link" href="liqboletos.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                            Liquidaciones x Bol
                        </a>
                        <a class="nav-link active" href="#">
                            <div class="sb-nav-link-icon active"><i class="fas fa-tachometer-alt"></i></div>
                            Liquidaciones x Docu
                        </a>
                        
                        <div class="sb-sidenav-menu-heading">Reportes</div>
                            <a class="nav-link" href="reportes.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Boletos
                            </a>
                            <a class="nav-link" href="reportesdoc.php">
                                <div class="sb-nav-link-icon active"><i class="fas fa-tachometer-alt"></i></div>
                                Documentos
                            </a>
                    </div>
                </div>
                <div class="sb-sidenav-footer">
                    <div class="small">Registrado:</div>
                    Administrador
                </div>
            </nav>
        </div>
        <div id="layoutSidenav_content">
        <main>
            <form id="buscar-form" action="javascript:void(0);">
            <div class="container">
                <div class="row mt-4">
                    <div class="col-lg-12">
                        <h2 class="text-center mb-4">Liquidaciones por Documento</h2>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group row">
                            <label for="tipo_liquidacion" class="col-sm-4 col-form-label btn-sm">Tipo de Liquidación:</label>
                            <div class="col-sm-8">
                                <select class="form-control btn-sm" id="tipo_liquidacion" name="tipo_liquidacion" required>
                                    <option value="">[- Seleccione -]</option>
                                    <?php foreach ($tipos_liquidacion as $tipo): ?>
                                        <option value="<?php echo $tipo['co_tipo']; ?>"><?php echo $tipo['de_tipo_liquidacion']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group row">
                            <label for="nu_liquidacion" class="col-sm-4 col-form-label btn-sm">Número:</label>
                            <div class="col-sm-8">
                                <select class="form-control btn-sm" id="nu_liquidacion" name="nu_liquidacion">
                                    <option value="">[- Seleccione un tipo de liquidación -]</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-lg-12">
                        <div class="form-group row">
                            <label for="de_obs_control_doc" class="col-sm-2 col-form-label btn-sm">Glosa:</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control btn-sm" id="de_obs_control_doc" name="de_obs_control_doc" required>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row bg-light mt-4 p-3">
                    <div class="col-lg-6">
                        <div class="form-group row">
                            <label for="fecha1" class="col-sm-4 col-form-label btn-sm">Fecha del:</label>
                            <div class="col-sm-8">
                                <input type="date" class="form-control btn-sm" id="fecha1" name="fecha1" required>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group row">
                            <label for="fecha2" class="col-sm-4 col-form-label btn-sm text-center">al</label>
                            <div class="col-sm-8">
                                <input type="date" class="form-control btn-sm" id="fecha2" name="fecha2" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-12">
                    <div class="col-lg-3">
                        <button type="submit" class="btn btn-success btn-sm" id="buscar">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-upload" viewBox="0 0 16 16">
                            <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                            <path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708z"/>
                        </svg> CARCAR REGISTROS
                        </button>
                    </div>
                    <div class="col-lg-3">
                        <button type="button" class="btn btn-warning btn-sm" id="limpiar" onclick="limpiarFormulario()">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eraser" viewBox="0 0 16 16">
                            <path d="M8.086 1.672a1 1 0 0 1 1.415 0l1.415 1.415a1 1 0 0 1 0 1.415L9.207 6.293l-2.828-2.828L8.086 1.672zM4.5 7.5L8.086 11.086a1 1 0 0 1 0 1.415L6.672 14.086a1 1 0 0 1-1.415 0L2.5 11.415a1 1 0 0 1 0-1.415L4.5 7.5zm-2.415 6.086a.5.5 0 0 1 0-.707L1.793 12.5H4.5a.5.5 0 0 1 0 1H1.793l-.707.707a.5.5 0 0 1-.707 0z"/>
                        </svg>LIMPIAR FORMULARIO</button>
                    </div>
                    <div class="col-lg-6">
                        <button type="button" class="btn btn-primary btn-sm" id="liquidar" style="display: none;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-upload" viewBox="0 0 16 16">
                            <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                            <path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708z"/>
                        </svg> CARGAR LIQUIDACIONES
                        </button>
                    </div>
                </div>
                <div class="row mt-4">
                    <div class="col-lg-12">
                        <div id="resultados"></div>
                    </div>
                </div>
            </div>
            </form>
        </main>
        <footer class="py-4 bg-light mt-auto">
            <div class="container-fluid px-4">
                <div class="d-flex align-items-center justify-content-between small">
                    <div class="text-muted">Copyright &copy; AC Tours</div>
                    <div>
                        2024
                        &middot;
                        Todos los derechos reservados.
                    </div>
                </div>
            </div>
        </footer>
    </div>
    <script>
       $(document).ready(function(){
            $('#tipo_liquidacion').change(function(){
                var tipoSeleccionado = $(this).val();
                $.ajax({
                    url: 'obtener_nu_liquidacion2.php',
                    method: 'POST',
                    data: {co_tipo: tipoSeleccionado},
                    success: function(response){
                        var options = '';
                        for (var i = response; i >= 1; i--) {
                            options += '<option value="' + i + '">' + i + '</option>';
                        }
                        $('#nu_liquidacion').html(options);
                    }
                });
            });

            $('#fecha1').on('change', function() {
                var fecha1Val = $(this).val();
                $('#fecha2').val(fecha1Val);
                $('#fecha2').focus(); 
            });

            $('#buscar-form').submit(function() {
                var tipo_liquidacion = $('#tipo_liquidacion').val();
                var fecha1 = $('#fecha1').val();
                var fecha2 = $('#fecha2').val();
                var tipo_boleto = $('#tipo_boleto').val();
                var codigo_referencia = $('#codigo_referencia').val();

                $.ajax({
                    url: 'obtener_documentosdoc.php',
                    method: 'POST',
                    data: {
                        tipo_liquidacion: tipo_liquidacion,
                        fecha1: fecha1,
                        fecha2: fecha2,
                        tipo_boleto: tipo_boleto,
                        codigo_referencia: codigo_referencia
                    },
                    success: function(response) {
                        var documentos = JSON.parse(response);
                        var tabla = '<table class="table table-striped table-sm">';
                        tabla += '<thead><tr><th>Documento</th><th>Tipo Doc</th><th>Serie</th><th>Número</th><th>File</th><th>Fecha</th><th>Monto</th></tr></thead><tbody>';
                        
                        var sumatotal = 0;
                        
                        $.each(documentos, function(index, documento) {
                            var monto = parseFloat(documento.mo_total);
                            sumatotal += monto;
                            tabla += '<tr>';
                            tabla += '<td><span class="badge bg-primary">' + documento.nu_docu + '</span></td>';
                            tabla += '<td>' + documento.co_tip_doc + '</td>';
                            tabla += '<td>' + documento.nu_serie + '</td>';
                            tabla += '<td>' + documento.nu_docu + '</td>';
                            tabla += '<td>' + documento.nu_file + '</td>';
                            tabla += '<td>' + documento.fe_docu + '</td>';
                            tabla += '<td class="text-right">' + monto.toFixed(2) + '</td>';
                            tabla += '</tr>';
                        });

                        tabla += '<tr>';
                        tabla += '<td colspan="6" class="text-right"><strong>Total:</strong></td>';
                        tabla += '<td class="text-right"><strong>' + sumatotal.toFixed(2) + '</strong></td>';
                        tabla += '</tr>';

                        tabla += '</tbody></table>';
                        $('#resultados').html(tabla);

                        $('#liquidar').show();

                        Swal.fire({
                            toast: true,
                            position: 'top',
                            icon: 'success',
                            title: 'Se han encontrado ' + documentos.length + ' registros.',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    }
                });

                return false;
            });

            $('#liquidar').click(function() {
            var nu_liquidacion = $('#nu_liquidacion').val();
            var tipo_liquidacion = $('#tipo_liquidacion').val();
            var fecha1 = $('#fecha1').val();
            var fecha2 = $('#fecha2').val();
            var tipo_boleto = $('#tipo_boleto').val();
            var codigo_referencia = $('#codigo_referencia').val();

            $.ajax({
                url: 'liquidar_busquedadoc.php',
                method: 'POST',
                data: {
                    nu_liquidacion: nu_liquidacion,
                    tipo_liquidacion: tipo_liquidacion,
                    fecha1: fecha1,
                    fecha2: fecha2,
                    tipo_boleto: tipo_boleto,
                    codigo_referencia: codigo_referencia
                },
                success: function(response) {
                    var data = JSON.parse(response);
                    if (data.error) {
                        $('#resultados').html('<div class="alert alert-danger" role="alert">' + data.error + '</div>');
                            Swal.fire({
                                toast: true,
                                position: 'top-right',
                                icon: 'error',
                                title: 'Registros con liquidación.',
                                showConfirmButton: false,
                                timer: 3000
                            });
                        return;
                    } else if (data.success) {
                        $('#resultados').html('<div class="alert alert-success" role="alert">' + data.success + '</div>');
                            Swal.fire({
                                toast: true,
                                position: 'top-right',
                                icon: 'success',
                                title: 'Se actualizaron correctamente ' + data.success + ' liquidaciones.',
                                showConfirmButton: false,
                                timer: 3000
                            });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error en la petición AJAX: ' + error);
                }
            });
        });
        });
        function limpiarFormulario() {
            document.getElementById('buscar-form').reset();
            document.getElementById('resultados').innerHTML = '';
            var nuLiquidacion = document.getElementById('nu_liquidacion');
            nuLiquidacion.value = '';
            $('#liquidar').hide();
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
    <script src="assets/demo/chart-area-demo.js"></script>
    <script src="assets/demo/chart-bar-demo.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
    <script src="js/datatables-simple-demo.js"></script>
</body>
</html>