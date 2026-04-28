$host = "localhost";
$user = "lihat-aku";
$password = "tititgede";
$database = "cofg1625_Roumah_kitchen";

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error()); }