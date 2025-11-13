       <?php
        /*$host = "localhost";
        $user = "root";     // cambia si tienes otro usuario
        $pass = "";         // cambia si tu MySQL tiene contraseña
        $db   = "sistema_login";

        $conn = new mysqli($host, $user, $pass, $db);

        if ($conn->connect_error) {
            die("❌ Error de conexión: " . $conn->connect_error);
        }*/
              
        // Si Render proporciona las variables de entorno, se usan.
        // Si estás en local, se usan tus valores por defecto.
        $host = getenv('DB_HOST') ?: 'localhost';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASS') ?: '';
        $db   = getenv('DB_NAME') ?: 'sistema_login';

        $conn = new mysqli($host, $user, $pass, $db);

        if ($conn->connect_error) {
            die("❌ Error de conexión: " . $conn->connect_error);
        }


        ?>

