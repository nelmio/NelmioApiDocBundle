<li class="<?php echo strtolower($method); ?> operation">
    <div class="heading toggler">
    <h3>
        <span class="http_method">
            <a><?php echo $method; ?></a>
        </span>
        <span class="path">
            <?php echo $uri; ?>
        </span>
    </h3>
    <ul class="options">
        <?php if (isset($description)) : ?>
        <li><?php echo $description; ?></li>
        <?php endif; ?>
    </ul>
    </div>
    <div class="content" style="display: none;">
    <?php if (isset($requirements) && !empty($requirements)) : ?>
        <h4>Requirements</h4>
        <table class="fullwidth">
        <thead>
            <tr>
            <th>Name</th>
            <th>Value</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($requirements as $key => $value) : ?>
            <tr>
            <td><?php echo $key; ?></td>
            <td><?php echo $value; ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        </table>
    <?php endif; ?>

    <?php if (isset($filters)) : ?>
        <h4>Filters</h4>
        <table class="fullwidth">
        <thead>
            <tr>
            <th>Name</th>
            <th>Information</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($filters as $name => $info) : ?>
            <tr>
            <td><?php echo $name; ?></td>
            <td>
                <ul>
                <?php foreach ($info as $key => $value) : ?>
                    <li><em><?php echo $key; ?></em> : <?php echo $value; ?></li>
                <?php endforeach; ?>
                </ul>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
        </table>
    <?php endif; ?>

    <?php if (isset($parameters)) : ?>
        <h4>Parameters</h4>
        <table class='fullwidth'>
        <thead>
            <tr>
            <th>Parameter</th>
            <th>Type</th>
            <th>Required?</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($parameters as $name => $info) : ?>
            <tr>
            <td><?php echo $name; ?></td>
            <td><?php echo $info['dataType']; ?></td>
            <td><?php echo $info['required'] ? 'true' : 'false'; ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
        </table>
    <?php endif; ?>
    </div>
</li>
