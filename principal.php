<?php
//include "conexion.php";
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
                <li><a class="dropdown-item" href="#!">Configuración</a></li>
                <li><a class="dropdown-item" href="#!">Actividad</a></li>
                <li><hr class="dropdown-divider" /></li>
                <li><a class="dropdown-item" href="#!">Cerrar sesión</a></li>
            </ul>
        </li>
    </ul>
</nav>
<div id="layoutSidenav">
    <div id="layoutSidenav_nav">
        <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
            <div class="sb-sidenav-menu">
                <div class="nav">
                    <div class="sb-sidenav-menu-heading">Inicio</div>
                    <a class="nav-link" href="index.html">
                        <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                        Dashboard
                    </a>
                    <div class="sb-sidenav-menu-heading">Acciones</div>
                    <a class="nav-link" href="layouts-liquidaciones.html">
                        <div class="sb-nav-link-icon"><i class="fas fa-chart-area"></i></div>
                        Liquidaciones
                    </a>
                    <a class="nav-link" href="layouts-control-doc.html">
                        <div class="sb-nav-link-icon"><i class="fas fa-table"></i></div>
                        Control Documentario
                    </a>
                </div>
            </div>
            <div class="sb-sidenav-footer">
                <div class="small">Logueado como:</div>
                Usuario
            </div>
        </nav>
    </div>
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4">
                <h1 class="mt-4">Control Documentario</h1>
                <ol class="breadcrumb mb4">
                    <li class="breadcrumb-item"><a href="index.html">Inicio</a></li>
                    <li class="breadcrumb-item active">Control Documentario</li>
                </ol>
                <div class="card mb-4">
                    <div class="card-body">
                        A continuación, suba un archivo Excel para actualizar las liquidaciones.
                    </div>
                </div>
                <div class="container mt-5">
                    <div class="row">
                        <div class="col-lg-8 mx-auto">
                            <div class="card">
                                <div class="card-header">
                                    <h4>Subir archivo Excel</h4>
                                </div>
                                <div class="card-body">
                                    <form id="uploadForm" enctype="multipart/form-data">
                                        <div class="form-group">
                                            <label for="excelFile">Seleccione el archivo Excel:</label>
                                            <input type="file" class="form-control" id="excelFile" name="excelFile" accept=".xlsx, .xls" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="nu_liquidacion">N° de Liquidación:</label>
                                            <input type="text" class="form-control" id="nu_liquidacion" name="nu_liquidacion" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label for="tipo_liquidacion">Tipo de Liquidación:</label>
                                            <select class="form-control" id="tipo_liquidacion" name="tipo_liquidacion" required>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="obs">Observaciones:</label>
                                            <textarea class="form-control" id="obs" name="obs" rows="3"></textarea>
                                        </div>
                                        <button type="button" id="guardarDatos" class="btn btn-primary">Subir</button>
                                    </form>
                                </div>
                            </div>
                            <div class="mt-3" id="result"></div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script src="js/scripts.js"></script>
<script>
        $(document).ready(function() {
            $.ajax({
            url: 'http://REMOTESERVER:9091/api/liquidacion/tipos-liquidacion',
            method: 'GET',
            success: function(response) {
                console.log(response); 
                var tipos = response;
                var $tipoLiquidacion = $('#tipo_liquidacion');
                $tipoLiquidacion.empty();
                tipos.forEach(function(tipo) {
                    $tipoLiquidacion.append('<option value="' + tipo.co_tipo + '">' + tipo.de_tipo_liquidacion + '</option>');
                });
            },
            error: function(xhr, status, error) {
                console.error('Error:', status, error);
                Swal.fire({
                    title: 'Error',
                    text: 'No se pudieron cargar los tipos de liquidación.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        });

        $('#tipo_liquidacion').on('change', function() {
            var co_tipo = $(this).val();
            var url = 'http://REMOTESERVER:9091/api/liquidacion/max-nu-liquidacion/' + co_tipo;
            
            $.ajax({
                url: url,
                method: 'GET',
                success: function(response) {
                    $('#nu_liquidacion').val(response.max_nu_liquidacion);
                },
                error: function(xhr, status, error) {
                    console.error('Error:', status, error);
                    Swal.fire({
                        title: 'Error',
                        text: 'No se pudo obtener el número de liquidación.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        });

        $('#uploadForm').on('submit', function(e) {
            e.preventDefault();
            var formData = new FormData(this);

            $.ajax({
                url: 'upload.php',
                method: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    $('#result').html(response);
                },
                error: function() {
                    Swal.fire({
                        title: 'Error',
                        text: 'No se pudo subir el archivo.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        });
        $('#guardarDatos').on('click', function () {
            var nuLiquidacion = $('#nu_liquidacion').val();
            var tipoLiquidacion = $('#tipo_liquidacion').val();
            var observaciones = $('#de_obs_control_doc').val();

            if (nuLiquidacion.trim() === '' || tipoLiquidacion.trim() === '' || observaciones === '') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Advertencia',
                    text: 'Debe seleccionar el tipo de liquidación, el número de liquidación e ingresar sus observaciones.',
                });
            } else {
                document.getElementById('liquidacion').value = nuLiquidacion;
                document.getElementById('tipo').value = tipoLiquidacion;
                document.getElementById('obs').value = observaciones;
                $('#guardarDatos').off('click');
                $('#uploadForm1').submit();
            }
        });
                        $('#uploadForm1').submit(function(e){
                            e.preventDefault();

                            var error = false;
                            $('#uploadForm1 input[type="text"]').each(function(){
                                if($(this).val() === ''){
                                    error = true;
                                    $(this).addClass('is-invalid');
                                }
                                else{
                                    $(this).removeClass('is-invalid');
                                }
                            });

                            if(error) {
                                Swal.fire({
                                    title: 'Error',
                                    text: 'Por favor complete todos los campos del formulario',
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                                return;
                            }

                            $('#loader').show();
                            var formData = new FormData(this);
                            var nuLiquidacion = $('#nu_liquidacion').val();
                            var tipoLiquidacion = $('#tipo_liquidacion').val();
                            formData.append('nu_liquidacion', nuLiquidacion);
                            formData.append('tipo_liquidacion', tipoLiquidacion);
                            $.ajax({
                                url: 'send_data.php',
                                type: 'POST',
                                data: formData,
                                contentType: false,
                                processData: false,
                                success: function(response){
                                    $('#loader').hide();
                                    var message = JSON.parse(response).message;
                                    var alertClass = 'alert-success';
                                    if (message.startsWith('Error')) {
                                        alertClass = 'alert-danger';
                                    }
                                    var formattedMessage = '<div class="alert ' + alertClass + '">' + message + '</div>';
                                    $('#resultados').html(formattedMessage);
                                    $('#guardarDatos').show();
                                    $('#nu_liquidacion').val('');
                                    $('#tipo_liquidacion').val('');
                                    $('#de_obs_control_doc').val('');
                                    $('#excelFile').val('');
                                    $('#contenido').hide();
                                    $('#nuevoIntento').show();
                                },
                                error: function(xhr, status, error){
                                    $('#loader').hide();
                                    var errorMessage = 'Hubo un error al cargar el archivo: ' + error;
                                    var formattedErrorMessage = '<div class="alert alert-danger">' + errorMessage + '</div>';
                                    $('#resultados').html(formattedErrorMessage);
                                    $('#guardarDatos').show();
                                    $('#nu_liquidacion').val('');
                                    $('#tipo_liquidacion').val('');
                                    $('#de_obs_control_doc').val('');
                                    $('#excelFile').val('');
                                }
                            });
                        });
    });
</script>
</body>
</html>