<!DOCTYPE html>
<html lang="ru">
    <head>
        <meta charset="utf-8">
        <title>Апи</title>
    </head>
    <body>
        <?php
        require './Api.php';
        $apiUrl = 'http://api/';
        $api = new Api($apiUrl);

        $query = function($type, $url, array $data = []) use($api, $apiUrls) {
            echo '<br>';
            echo strtoupper($type) . ' ' . $apiUrl . $url;
            echo '<br>';
            if (!empty($data)) {
                echo 'Params: ' . var_export($data, true);
                echo '<br>';
            }
            $result = $api->$type($url, $data);
            echo 'Result: ' . $result;
            echo '<br><br><br>';
            return $result;
        };

        $user = ['email' => 'john@example.com', 'name' => 'John', 'password' => '123456',];

        echo 'Создание пользователя';
        $userResult = $query('post', 'user', $user);
        $api->setToken(json_decode($userResult)->success->token);

        echo 'Запрос нового токена';
        $tokenResult = $query('get', 'token', $user);
        $token = json_decode($tokenResult)->success->token;
        $api->setToken($token);

        echo 'Создание новой категории с неправильным токеном';
        $api->setToken('wrongtoken');
        $query('post', 'categories', [ 'name' => 'Category 3',]);
        $api->setToken($token);

        echo '<b>Создание новой категории</b>';
        $query('post', 'categories', [ 'name' => 'Category 3',]);
        
        echo 'Создание новой категории';
        $query('post', 'categories', [ 'name' => 'Category 2',]);

        echo '<b>Просмотр списка, отсортированного по алфавиту (asc)</b>';
        $query('get', 'categories/asc');

        echo '<b>Ппросмотр списка, отсортированного по алфавиту (desc)</b>';
        $categoriesResult = $query('get', 'categories/desc');
        foreach (json_decode($categoriesResult)->success as $category) {
            $catIds[] = $category->id;
        }

        echo '<b>Редактирование названия категории</b>';
        $query('put', 'categories/' . $catIds[0], ['name' => 'Category 1',]);

        echo '<b>Cоздание продукта</b>';
        $query('post', 'products', [ 'name' => 'Product 4', 'category_id' => $catIds[0]]);

        echo 'Cоздание продукта';
        $query('post', 'products', [ 'name' => 'Product 2', 'category_id' => $catIds[0]]);

        echo 'Cоздание продукта';
        $query('post', 'products', [ 'name' => 'Product 3', 'category_id' => $catIds[1]]);

        echo '<b>Просмотр списка продуктов для заданной категории</b> (' . $catIds[0] . ')';
        $productsResult0 = $query('get', 'products/asc', ['category_id' => $catIds[0]]);
        foreach (json_decode($productsResult0)->success as $product) {
            $prodIds[$catIds[0]][] = $product->id;
        }

        echo 'Просмотр списка продуктов для заданной категории (' . $catIds[1] . ')';
        $productsResult1 = $query('get', 'products', ['category_id' => $catIds[1]]);
        foreach (json_decode($productsResult1)->success as $product) {
            $prodIds[$catIds[1]][] = $product->id;
        }

        echo '<b>Редактирование названия продукта</b> (' . $prodIds[$catIds[0]][1] . ')';
        $query('put', 'products/' . $prodIds[$catIds[0]][1], ['name' => 'Product 1',]);

        echo 'Просмотр списка продуктов для заданной категории (' . $catIds[0] . ') (desc)';
        $query('get', 'products/desc', ['category_id' => $catIds[0]]);

        echo '<b>Редактирование категории продукта</b> (' . $prodIds[$catIds[0]][1] . ')';
        $query('put', 'products/' . $prodIds[$catIds[0]][1], ['category_id' => $catIds[1],]);

        echo 'Просмотр списка продуктов для заданной категории (' . $catIds[0] . ') (desc)';
        $query('get', 'products/desc', ['category_id' => $catIds[0]]);

        echo 'Просмотр списка продуктов для заданной категории (' . $catIds[1] . ') (desc)';
        $query('get', 'products/desc', ['category_id' => $catIds[1]]);

        echo '<b>Просмотр деталей продукта</b> (' . $prodIds[$catIds[0]][0] . ')';
        $query('get', 'products/' . $prodIds[$catIds[0]][0]);
        
        echo '<b>Удаление продукта</b> (' . $prodIds[$catIds[0]][0] . ')';
        $query('delete', 'products/' . $prodIds[$catIds[0]][0]);

        echo 'Просмотр деталей продукта (' . $prodIds[$catIds[0]][0] . ')';
        $query('get', 'products/' . $prodIds[$catIds[0]][0]);

        echo 'Просмотр списка продуктов для заданной категории (' . $catIds[0] . ')';
        $query('get', 'products/desc', ['category_id' => $catIds[0]]);

        echo '<b>Просмотр деталей категории</b> (' . $catIds[1] . ')';
        $query('get', 'categories/' . $catIds[1]);

        echo '<b>Удаление категории</b> (' . $catIds[1] . ')';
        $query('delete', 'categories/' . $catIds[1]);

        echo 'Просмотр деталей категории (' . $catIds[1] . ')';
        $query('get', 'categories/' . $catIds[1]);

        echo 'Удаление пользователя (и всего, чему он хозяин)';
        $query('delete', 'user');

        ?>
    </body>
</html>