<?php
include "conexion.php";

try {
    $sql = "SELECT co_tipo, de_tipo_liquidacion FROM TIPO_LIQUIDACION";
    $stmt = $conn->query($sql);
    $tipos_liquidacion = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                                <a class="nav-link" href="liqdocumentos.php">
                                    <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                    Liquidaciones x Doc
                                </a>
                            <div class="sb-sidenav-menu-heading">Reportes</div>
                                <a class="nav-link" href="reportes.php">
                                    <div class="sb-nav-link-icon active"><i class="fas fa-tachometer-alt"></i></div>
                                    Boletos
                                </a>
                                <a class="nav-link active" href="reportesdoc.php">
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
                <div class="container">
                    <div class="row mt-4">
                        <div class="col-lg-12">
                            <h2 class="text-center mb-4">Reportes por documentos</h2>
                        </div>
                    </div>    
                    <div class="row bg-light">
                        <div class="col-lg-4 mt-4 mb-1">
                            <div class="form-group">
                                <label for="tipo_liquidacion">Tipo de Liquidación</label>
                                <select class="form-control" id="tipo_liquidacion" name="tipo_liquidacion">
                                    <option value="">[- Seleccione -]</option>
                                    <?php foreach ($tipos_liquidacion as $tipo): ?>
                                        <option value="<?php echo $tipo['co_tipo']; ?>"><?php echo $tipo['de_tipo_liquidacion']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-4 mt-4 mb-1">
                            <label for="tipo_liquidacion">Número:</label>
                            <select class="form-control" id="nu_liquidacion" name="nu_liquidacion">
                                <option value="">[- Seleccione un tipo de liquidación -]</option>
                            </select>
                        </div>
                        <div class="col-lg-4 mt-4 mb-1">
                            <form id="uploadForm1" enctype="multipart/form-data">
                                <div class="form-group-hidden mt-1">
                                    <input type="file" class="form-control-file" id="excelFile1" name="excelFile">
                                </div>
                                <label for="generarReporte">Reporte</label>
                                <button type="button" class="btn btn-info btn-block" id="generarReporte"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-database-up" viewBox="0 0 16 16">
  <path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7m.354-5.854 1.5 1.5a.5.5 0 0 1-.708.708L13 11.707V14.5a.5.5 0 0 1-1 0v-2.793l-.646.647a.5.5 0 0 1-.708-.708l1.5-1.5a.5.5 0 0 1 .708 0"/>
  <path d="M12.096 6.223A5 5 0 0 0 13 5.698V7c0 .289-.213.654-.753 1.007a4.5 4.5 0 0 1 1.753.25V4c0-1.007-.875-1.755-1.904-2.223C11.022 1.289 9.573 1 8 1s-3.022.289-4.096.777C2.875 2.245 2 2.993 2 4v9c0 1.007.875 1.755 1.904 2.223C4.978 15.71 6.427 16 8 16c.536 0 1.058-.034 1.555-.097a4.5 4.5 0 0 1-.813-.927Q8.378 15 8 15c-1.464 0-2.766-.27-3.682-.687C3.356 13.875 3 13.373 3 13v-1.302c.271.202.58.378.904.525C4.978 12.71 6.427 13 8 13h.027a4.6 4.6 0 0 1 0-1H8c-1.464 0-2.766-.27-3.682-.687C3.356 10.875 3 10.373 3 10V8.698c.271.202.58.378.904.525C4.978 9.71 6.427 10 8 10q.393 0 .774-.024a4.5 4.5 0 0 1 1.102-1.132C9.298 8.944 8.666 9 8 9c-1.464 0-2.766-.27-3.682-.687C3.356 7.875 3 7.373 3 7V5.698c.271.202.58.378.904.525C4.978 6.711 6.427 7 8 7s3.022-.289 4.096-.777M3 4c0-.374.356-.875 1.318-1.313C5.234 2.271 6.536 2 8 2s2.766.27 3.682.687C12.644 3.125 13 3.627 13 4c0 .374-.356.875-1.318 1.313C10.766 5.729 9.464 6 8 6s-2.766-.27-3.682-.687C3.356 4.875 3 4.373 3 4"/>
</svg> Generar</button>
                            </form>
                        </div>
                    </div>
                    <div id="loader" class="text-center" style="display: none;">
                        <img src="uploads/upload.gif" alt="Cargando...">
                    </div>
                    <div id="resultados" class="mt-5">
                    </div>
                </div>

                <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
                <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
                <script>
                    function copyFile() {
                        var fileInput = document.getElementById('excelFile');
                        var fileInput1 = document.getElementById('excelFile1');
                        fileInput1.files = fileInput.files;
                    }
                    $(document).ready(function(){
                        $('#tipo_liquidacion').change(function(){
                            var tipoSeleccionado = $(this).val();
                            $.ajax({
                                url: 'obtener_nu_liquidacion1.php',
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
                        $('#generarReporte').on('click', function () {
                            var nuLiquidacion = $('#nu_liquidacion').val();
                            var tipoLiquidacion = $('#tipo_liquidacion').val();
                            var observaciones = $('#de_obs_control_doc').val();

                            if (nuLiquidacion.trim() === '' || tipoLiquidacion.trim() === '' || observaciones === '') {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Advertencia',
                                    text: 'Debe seleccionar el tipo de liquidación y el número de liquidación.',
                                });
                            } else {
                                var formData = {
                                    nu_liquidacion: nuLiquidacion,
                                    tipo_liquidacion: tipoLiquidacion,
                                    de_obs_control_doc: observaciones
                                };
                                $.ajax({
                                    url: 'generar_reporte_doc.php',
                                    type: 'POST',
                                    data: formData,
                                    success: function(response) {
                                        window.location.href = response;
                                    },
                                    error: function() {
                                        alert('Error al generar el reporte.');
                                    }
                                });
                            }
                        });
                    });
                </script>
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
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="assets/demo/chart-area-demo.js"></script>
        <script src="assets/demo/chart-bar-demo.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
        <script src="js/datatables-simple-demo.js"></script>
    </body>
</html>