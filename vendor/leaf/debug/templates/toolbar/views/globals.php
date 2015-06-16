<ul>
    <?php foreach ($globals as $name => $global):?>
    <li class="spoiler">
        <input type="checkbox" id="id_<?=$name?>">
        <label for="id_<?=$name?>"><?=$name?></label>
        <div class="spoiler-body"><?=$global?></div>
    </li>
    <?php endforeach;?>
</ul>
