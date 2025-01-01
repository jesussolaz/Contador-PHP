<?php
// Este código solo se ejecutará en Windows
if (stripos(PHP_OS, 'WIN') === false) {
    die("Este script solo está diseñado para ejecutarse en sistemas Windows.");
}

// Configurar la codificación UTF-8
ini_set('default_charset', 'UTF-8');
setlocale(LC_ALL, 'es_ES.UTF-8');

// Verificar e instalar PHP-GTK si no está instalado
if (!extension_loaded('php-gtk')) {
    echo "PHP-GTK no está instalado o no se detecta correctamente. Intentando instalarlo...\n";

    $installerPath = 'https://github.com/php/php-gtk-src/releases/download/latest/php-gtk2.zip';
    $tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'php-gtk';

    if (!is_dir($tempDir)) {
        mkdir($tempDir, 0777, true);
    }

    $zipFile = $tempDir . DIRECTORY_SEPARATOR . 'php-gtk2.zip';
    $downloaded = file_put_contents($zipFile, file_get_contents($installerPath));

    if ($downloaded === false) {
        die("No se pudo descargar PHP-GTK. Verifica tu conexión a internet.");
    }

    $zip = new ZipArchive();
    if ($zip->open($zipFile) === true) {
        $zip->extractTo($tempDir);
        $zip->close();

        $phpExtDir = ini_get('extension_dir');
        $dllFiles = glob($tempDir . DIRECTORY_SEPARATOR . '*.dll');

        foreach ($dllFiles as $dllFile) {
            copy($dllFile, $phpExtDir . DIRECTORY_SEPARATOR . basename($dllFile));
        }

        echo "PHP-GTK instalado correctamente. Por favor, reinicia PHP.\n";
        die();
    } else {
        die("No se pudo extraer el archivo ZIP de PHP-GTK.");
    }
}

// Crear la ventana principal
$window = new GtkWindow();
$window->set_title(utf8_decode("Aplicación de Contador"));
$window->set_size_request(800, 220);
$window->connect_simple('destroy', ['Gtk', 'main_quit']);

// Crear un contenedor horizontal para toda la estructura
$main_box = new GtkHBox();
$window->add($main_box);

// Crear un contenedor vertical para los campos de entrada
$fields_box = new GtkVBox();
$main_box->pack_start($fields_box, true, true, 10);

// Crear la fila de Cliente y Proyecto
$row1 = new GtkHBox();
$cliente_combo = new GtkComboBoxText();
$cliente_combo->append_text(utf8_decode("Seleccionar Cliente"));
$cliente_combo->set_active(0);
$cliente_button = new GtkButton("+");
$row1->pack_start($cliente_combo, true, true, 5);
$row1->pack_start($cliente_button, false, false, 5);

$proyecto_combo = new GtkComboBoxText();
$proyecto_combo->append_text(utf8_decode("Seleccionar Proyecto"));
$proyecto_combo->set_active(0);
$proyecto_button = new GtkButton("+");
$row1->pack_start($proyecto_combo, true, true, 5);
$row1->pack_start($proyecto_button, false, false, 5);
$fields_box->pack_start($row1, false, false, 10);

// Crear la fila del Título de la tarea
$titulo_entry = new GtkEntry();
$titulo_entry->set_text(utf8_decode("Título de la Tarea"));
$titulo_entry->connect('focus-in-event', function($widget) {
    if ($widget->get_text() === utf8_decode("Título de la Tarea")) {
        $widget->set_text("");
    }
});
$titulo_entry->connect('focus-out-event', function($widget) {
    if ($widget->get_text() === "") {
        $widget->set_text(utf8_decode("Título de la Tarea"));
    }
});
$fields_box->pack_start($titulo_entry, false, false, 10);

// Crear la fila de Notas
$notas_entry = new GtkTextView();
$notas_entry_buffer = $notas_entry->get_buffer();
$notas_entry_buffer->set_text(utf8_decode("Escribe tus notas aquí"));
$notas_entry->connect('focus-in-event', function($widget) use ($notas_entry_buffer) {
    if ($notas_entry_buffer->get_text($notas_entry_buffer->get_start_iter(), $notas_entry_buffer->get_end_iter(), false) === utf8_decode("Escribe tus notas aquí")) {
        $notas_entry_buffer->set_text("");
    }
});
$notas_entry->connect('focus-out-event', function($widget) use ($notas_entry_buffer) {
    if ($notas_entry_buffer->get_text($notas_entry_buffer->get_start_iter(), $notas_entry_buffer->get_end_iter(), false) === "") {
        $notas_entry_buffer->set_text(utf8_decode("Escribe tus notas aquí"));
    }
});
$notas_scrolled = new GtkScrolledWindow();
$notas_scrolled->set_policy(Gtk::POLICY_AUTOMATIC, Gtk::POLICY_AUTOMATIC);
$notas_scrolled->add($notas_entry);
$notas_scrolled->set_size_request(400, 100);
$fields_box->pack_start($notas_scrolled, false, false, 10);

// Crear el temporizador y botones
$timer_box = new GtkVBox();
$main_box->pack_start($timer_box, false, false, 10);

$timer_label = new GtkLabel("<span size='20000' color='orange'>00:00:00</span>");
$timer_label->set_use_markup(true);
$timer_frame = new GtkFrame(utf8_decode("Contador"));
$timer_frame->set_size_request(250, 150);
$timer_frame->add($timer_label);
$timer_box->pack_start($timer_frame, false, false, 10);

$button_box = new GtkHBox();
$start_button = new GtkButton("Iniciar");
$pause_button = new GtkButton("Pausar");
$stop_button = new GtkButton("Finalizar");
$button_box->pack_start($start_button, true, true, 5);
$button_box->pack_start($pause_button, true, true, 5);
$button_box->pack_start($stop_button, true, true, 5);
$timer_box->pack_start($button_box, false, false, 10);

// Funcionalidades del temporizador
$start_time = null;
$paused_time = 0;
$running = false;

global $timer_id;
$timer_id = null;

function update_timer($label) {
    global $start_time, $paused_time;
    if ($start_time === null) {
        return true;
    }
    $elapsed = microtime(true) - $start_time + $paused_time;
    $hours = floor($elapsed / 3600);
    $minutes = floor(($elapsed % 3600) / 60);
    $seconds = floor($elapsed % 60);
    $label->set_markup(sprintf("<span size='20000' color='orange'>%02d:%02d:%02d</span>", $hours, $minutes, $seconds));
    return true;
}

$start_button->connect('clicked', function() use ($timer_label, &$running, &$start_time, &$paused_time, &$timer_id) {
    if (!$running) {
        $running = true;
        $start_time = microtime(true);
        $timer_id = Gtk::timeout_add(1000, 'update_timer', $timer_label);
    }
});

$pause_button->connect('clicked', function() use (&$running, &$paused_time, &$timer_id, &$start_time) {
    if ($running) {
        $running = false;
        $paused_time += microtime(true) - $start_time;
        Gtk::timeout_remove($timer_id);
    } else {
        $running = true;
        $start_time = microtime(true);
        $timer_id = Gtk::timeout_add(1000, 'update_timer', $timer_label);
    }
});

$stop_button->connect('clicked', function() use ($timer_label, &$start_time, &$paused_time, &$running, &$timer_id) {
    $running = false;
    $start_time = null;
    $paused_time = 0;
    Gtk::timeout_remove($timer_id);
    $timer_label->set_markup("<span size='20000' color='orange'>00:00:00</span>");
});

// Mostrar la ventana
$window->show_all();
Gtk::main();
?>
