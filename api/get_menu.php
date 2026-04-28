<?php
include "../config/db.php";

header("Content-Type: application/json");

$data = [];

$menuQuery = mysqli_query($conn, "
    SELECT id, name, category, price, description, image, created_at
    FROM menu
    ORDER BY id ASC
");

while ($menu = mysqli_fetch_assoc($menuQuery)) {
    $menuId = (int)$menu["id"];

    $toppings = [];
    $toppingQuery = mysqli_query($conn, "
        SELECT t.id, t.name, t.price
        FROM menu_toppings mt
        INNER JOIN toppings t ON t.id = mt.topping_id
        WHERE mt.menu_id = $menuId
          AND t.is_active = 1
        ORDER BY t.name ASC
    ");

    while ($topping = mysqli_fetch_assoc($toppingQuery)) {
        $toppings[] = [
            "id" => (int)$topping["id"],
            "name" => $topping["name"],
            "price" => (int)$topping["price"]
        ];
    }

    $spicyLevels = [];
    $spicyQuery = mysqli_query($conn, "
        SELECT s.id, s.name, s.sort_order
        FROM menu_spicy_levels ms
        INNER JOIN spicy_levels s ON s.id = ms.spicy_level_id
        WHERE ms.menu_id = $menuId
          AND s.is_active = 1
        ORDER BY s.sort_order ASC, s.id ASC
    ");

    while ($level = mysqli_fetch_assoc($spicyQuery)) {
        $spicyLevels[] = [
            "id" => (int)$level["id"],
            "name" => $level["name"],
            "sort_order" => (int)$level["sort_order"]
        ];
    }

    $menu["price"] = (int)$menu["price"];
    $menu["toppings"] = $toppings;
    $menu["spicy_levels"] = $spicyLevels;

    $data[] = $menu;
}

echo json_encode($data, JSON_UNESCAPED_UNICODE);
?>