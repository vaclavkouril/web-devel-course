<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>PHP Assignment - Table Filtering</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
</head>

<body class="container">
<h1 class="mt-4 mb-4">PHP Assignment - Table Filtering</h1>

<form action="?" method="GET">
    <div class="form-row align-items-end">
        <div class="form-group col-md-7">
            <label for="search">Search:</label>
            <input class="form-control" type="text" id="search" name="search" maxlength="100" value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="form-group col-md-2">
            <label for="maxprice">Max Price:</label>
            <input class="form-control" type="text" id="maxprice" name="maxprice" maxlength="6" pattern="^[0-9]+([.][0-9]{2})?$" placeholder="0.00"
                value="<?= htmlspecialchars($maxprice) ?>">
        </div>
        <div class="form-group col-md-2 text-center pb-2">
            <input class="form-check-input" type="checkbox" name="instock" value="1" id="instock" <?= htmlspecialchars(($instock ? 'checked' : '')) ?>>
            <label class="form-check-label" for="instock">In stock</label>
        </div>
        <div class="form-group col-md-1 text-right">
            <button type="submit" class="btn btn-primary">Filter</button>
        </div>
    </div>
</form>

<table class="table table-hover table-striped">
    <thead class="thead-dark">
        <tr>
            <th>Title</th>
            <th>Author</th>
            <th>Price</th>
            <th>Availability</th>
            <th>ISBN</th>
        </tr>
    </thead>
    <tbody>
		<?php foreach ($data as $obj) { ?>
        <tr>
            <td><?= htmlspecialchars($obj->title) ?></td>
            <td><?= htmlspecialchars($obj->author) ?></td>
            <td><?= htmlspecialchars($obj->price) ?></td>
            <td><?= htmlspecialchars($obj->availability) ?></td>
            <td><?= htmlspecialchars($obj->isbn) ?></td>
        </tr>
        <?php } ?>
    </tbody>
</table>

<?php if ($pagination_count > 1) { ?>
<nav>
    <ul class="pagination">
		<?php for ($p = 0; $p < $pagination_count; ++$p) { ?>
        <li class="page-item <?= htmlspecialchars(($p === $pagination_page) ? 'active' : '') ?>">
            <a class="page-link" href="?<?= htmlspecialchars($pagination_link($p)) ?>"><?= htmlspecialchars($p+1) ?></a>
        </li>
        <?php } ?>
    </ul>
</nav>
<?php } ?>

</body>

</html>
