<!DOCTYPE html>
<html lang="en">

<head>
    <title>Code Review Output</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
</head>

<body class="m-5">
    <div class="container-fluid">
        <nav>
            <div class="nav nav-tabs" id="nav-tab" role="tablist">
                <button class="nav-link active" id="nav-home-tab" data-bs-toggle="tab" data-bs-target="#nav-home" type="button" role="tab" aria-controls="nav-home" aria-selected="true">Controllers (<?= count($controllers); ?>)</button>
                <button class="nav-link" id="nav-profile-tab" data-bs-toggle="tab" data-bs-target="#nav-profile" type="button" role="tab" aria-controls="nav-profile" aria-selected="false">Models (<?= count($models); ?>)</button>
                <button class="nav-link" id="nav-contact-tab" data-bs-toggle="tab" data-bs-target="#nav-contact" type="button" role="tab" aria-controls="nav-contact" aria-selected="false">Contact</button>
            </div>
        </nav>
        <div class="tab-content" id="nav-tabContent">
            <div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
                <table id="controllers" class="table table-striped" aria-describedby="">
                    <thead>
                        <tr>
                            <th>File name</th>
                            <th>Size</th>
                            <th>Updated</th>
                            <th>Functions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($controllers as $controller) : ?>
                            <?php
                            if (is_array($controller["filename"]) || $controller["filename"] === 'index.html') {
                                continue; // This is a directory not a file or an index.html file
                            }
                            ?>
                            <tr>
                                <td><?= $controller["filename"] . ' (' . (isset($controller["functions_with_usage"]) ? (count($controller['functions_with_usage'])) : "(0)") . ')'; ?></td>
                                <td><?= number_to_size($controller["info"]["size"]); ?></td>
                                <td><?= date('d M Y', $controller["info"]["date"]); ?></td>
                                <td>
                                    <ol>
                                        <div class="accordion" id="accordion<?= str_replace(".php", "", $controller["filename"]); ?>">
                                            <?php if (isset($controller["functions_with_usage"])) {
                                                foreach ($controller["functions_with_usage"] as $function => $usage_array) : ?>
                                                    <div class="accordion-item">
                                                        <h2 class="accordion-header" id="heading<?= $function . str_replace(".php", "", $controller["filename"]); ?>">
                                                            <button class="accordion-button <?= !(is_array($usage_array['usage']) && !empty($usage_array['usage'])) ? "bg-warning text-black" : "" ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $function . str_replace(".php", "", $controller["filename"]); ?>" aria-expanded="true" aria-controls="collapse<?= $function . str_replace(".php", "", $controller["filename"]); ?>">
                                                                <strong>
                                                                    <em><?= $function; ?>()</em>
                                                                </strong>
                                                            </button>
                                                        </h2>
                                                        <div id="collapse<?= $function . str_replace(".php", "", $controller["filename"]); ?>" class="accordion-collapse collapse" aria-labelledby="heading<?= $function . str_replace(".php", "", $controller["filename"]); ?>" data-bs-parent="#accordionExample">
                                                            <div class="accordion-body">
                                                                <ul class="list-group">
                                                                    <?php if (is_array($usage_array['usage']) && !empty($usage_array['usage'])) : ?>
                                                                        <?php foreach ($usage_array['usage'] as $uses) : ?>
                                                                            <li class="list-group-item"><?= $uses; ?></li>
                                                                        <?php endforeach; ?>
                                                                    <?php else : ?>
                                                                        <li><span class="p-1 bg-warning rounded">Appears to be unused</span></li>
                                                                    <?php endif; ?>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                            <?php endforeach;
                                            } ?>
                                        </div>
                                    </ol>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">
                <table id="models" class="table table-striped" aria-describedby="">
                    <thead>
                        <tr>
                            <th>File name</th>
                            <th>Size</th>
                            <th>Updated</th>
                            <th>Functions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($models as $model) : ?>
                            <?php
                            if (is_array($model["filename"]) || $model["filename"] === 'index.html') {
                                continue; // This is a directory not a file or an index.html file
                            }
                            ?>
                            <tr>
                                <td><?= $model["filename"]; ?></td>
                                <td><?= number_to_size($model["info"]["size"]); ?></td>
                                <td><?= date('d M Y', $model["info"]["date"]); ?></td>
                                <td>
                                    <ol>
                                        <?php
                                        if (isset($model["functions_with_usage"])) {
                                            foreach ($model["functions_with_usage"] as $function => $usage_array) : ?>
                                                <li><?= $function; ?>()</li>
                                                <ul>
                                                    <?php if (is_array($usage_array['usage']) && !empty($usage_array['usage'])) : ?>
                                                        <?php foreach ($usage_array['usage'] as $uses) : ?>
                                                            <li><?= $uses; ?></li>
                                                        <?php endforeach; ?>
                                                    <?php else : ?>
                                                        <li><span class="label label-warning">Appears to be unused</span></li>
                                                    <?php endif; ?>
                                                </ul>
                                        <?php endforeach;
                                        } ?>
                                    </ol>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="tab-pane fade" id="nav-contact" role="tabpanel" aria-labelledby="nav-contact-tab">...</div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.min.js" integrity="sha384-ODmDIVzN+pFdexxHEHFBQH3/9/vQ9uori45z4JjnFsRydbmQbmL5t1tQ0culUzyK" crossorigin="anonymous"></script>
    <script>
        $(document).ready(function() {
            $('#controllers').DataTable();
            $('#models').DataTable();
        });
    </script>
</body>

</html>