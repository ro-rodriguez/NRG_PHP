<?php

use App\Auth\Autenticacion;
use App\Models\Testimonio;
use App\Validators\TestimonioPublicarValidacion;

require_once __DIR__ . '/../../bootstrap/init.php';

//Eliminamos los require de las clases y creamos la función autoload en init.php
//require_once RUTA_RAIZ . '/clases/Conexion.php';
//require_once RUTA_RAIZ . '/clases/Testimonio.php';
//require_once RUTA_RAIZ . '/clases/TestimonioPublicarValidacion.php';

//Verificamos que el usuario esté autenticado
$autenticacion = new Autenticacion();
if(!$autenticacion->estaAutenticado()) {
    $_SESSION['mensaje_error'] = "Para realizar esta acción primero debes iniciar sesión.";
    header("Location: ../index.php?s=login");
    exit;
}
/*
 |--------------------------------------------------------------------------
 | Capturamos los datos del form
 |--------------------------------------------------------------------------
 */
$id                     = $_POST['id'];
$nombre                 = $_POST['nombre'];
$texto                  = $_POST['texto'];
$localidad              = $_POST['localidad'];
$provincia              = $_POST['provincia'];
$profesion              = $_POST['profesion'];
$imagen_descripcion     = $_POST['imagen_descripcion'];
$imagen_actual          = $_POST['imagen_actual'];
$imagen                 = $_FILES['imagen'];

/*
 |--------------------------------------------------------------------------
 | Validación
 |--------------------------------------------------------------------------
 */
$validator = new TestimonioPublicarValidacion($_POST);
$validator->ejecutar();

if($validator->hayErrores()) {
    $_SESSION['errores'] = $validator->getErrores();
    $_SESSION['old_data'] = $_POST;
    header("Location: ../index.php?s=testimonios-nuevo");
    exit;
}
/*
 |--------------------------------------------------------------------------
 | Buscamos el testimonios actual
 |--------------------------------------------------------------------------
 */
$testimonioActual = (new Testimonio())->traerPorPk($id);

/*
 |--------------------------------------------------------------------------
 | Imagen
 |--------------------------------------------------------------------------
 */
if(!empty($imagen['tmp_name'])) {
    $imagen_nombre = date('YmdHis_') . $imagen['name'];
    move_uploaded_file($imagen['tmp_name'], RUTA_IMAGENES . DIRECTORY_SEPARATOR . $imagen_nombre);

} else {
    $imagen_nombre = $testimonioActual ->getImagen();
}

try {
    $testimonio = new Testimonio();
    $testimonio->editar($id, [
        'nombre' => $nombre,
        'texto' => $texto,
        'localidad' => $localidad,
        'provincia' => $provincia,
        'profesion' => $profesion,
        'imagen' => $imagen_nombre, 
        'imagen_descripcion' => $imagen_descripcion, 
        'usuario_fk' => 1, // TODO: Reemplazar con el id del usuario autenticado.
    ]);

    if(!empty($imagen['tp_name'])) {
        unlink(RUTA_IMAGENES . DIRECTORY_SEPARATOR . $testimonioActual -> getImagen());
    }
/*
 |--------------------------------------------------------------------------
 | Redireccionamos al usuario a otra pantalla
 |--------------------------------------------------------------------------
 */
    $_SESSION['mensaje_exito'] = "¡Excelente! El testimonio fue actualizado con éxito.";
    header('Location: ../index.php?s=testimonios');

} catch(Exception $e) {
    $_SESSION['mensaje_error'] = "¡Error! El testimonio no pudo ser actualizado. Por favor, intentalo más tarde.";
    header('Location: ../index.php?s=testimonios-nuevo');
}

