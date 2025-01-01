# Aplicación de Contador con PHP-GTK

Una sencilla aplicación de escritorio para gestionar proyectos y tareas, desarrollada utilizando PHP-GTK.

## Instalación

Sigue los pasos a continuación para instalar y configurar la aplicación:

### Requisitos

- PHP 5.5 o superior
- Extensión PHP-GTK instalada
- Sistema operativo Windows

### Pasos de instalación

1. **Clona el repositorio**:
   ```bash
   git clone https://github.com/jesussolaz/Contador-PHP
   cd contador
   ```

2. **Asegúrate de que PHP-GTK está instalado**:
   La aplicación verifica automáticamente si PHP-GTK está instalado. Si no lo está, intentará descargarlo e instalarlo.

   Si necesitas instalarlo manualmente:
   - Descarga la extensión desde [PHP-GTK Releases](https://github.com/php/php-gtk-src/releases).
   - Extrae los archivos y colócalos en el directorio de extensiones de PHP (`ext/`).
   - Activa la extensión agregando `extension=php_gtk2.dll` en tu archivo `php.ini`.

3. **Configura PHP para UTF-8**:
   Asegúrate de que tu archivo `php.ini` tiene configurada la codificación predeterminada:
   ```ini
   default_charset = "UTF-8"
   ```

## Ejecución

Sigue los pasos para ejecutar la aplicación:

1. **Navega al directorio del proyecto**:
   ```bash
   cd contador
   ```

2. **Ejecuta el script con PHP**:
   ```bash
   php contador_v2.php
   ```

3. **Interfaz gráfica**:
   La aplicación se abrirá con la interfaz gráfica donde podrás gestionar clientes, proyectos y tareas.

## Problemas conocidos

- Si los caracteres con acentos o la letra ñ no se muestran correctamente, asegúrate de que la configuración de codificación UTF-8 está habilitada en tu entorno PHP.
- En caso de error al instalar PHP-GTK automáticamente, sigue las instrucciones de instalación manual mencionadas anteriormente.

---

**Nota:** Esta aplicación está diseñada para funcionar únicamente en sistemas Windows.
