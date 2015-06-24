<ul>
    <?php foreach ($messages as $key => $message):?>
    <li class="spoiler">
        <input type="checkbox" id="id_<?=$key?>">
        <label for="id_<?=$key?>"><?=$message['header']?></label>
        <?php if(isset($message['body'])):?>
        <div class="spoiler-body"><?=$message['body']?></div>
        <?php endif;?>
    </li>
    <?php endforeach;?>
</ul>
