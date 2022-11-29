@if(count(\Rikkei\Core\View\Breadcrumb::get()))
<ol class="breadcrumb">
    @foreach(\Rikkei\Core\View\Breadcrumb::get() as $bLink)
        <li>
            <?php if(isset($bLink['url'])): ?>
                <a href="<?php echo $bLink['url']; ?>">
            <?php endif; ?>
                    
            <?php if(isset($bLink['pre_text'])): ?>
                <?php echo $bLink['pre_text']; ?>
            <?php endif; ?>
            
            <span><?php echo $bLink['text']; ?></span>
                    
            <?php if(isset($bLink['after_text'])): ?>
                <?php echo $bLink['after_text']; ?>
            <?php endif; ?>
                    
            <?php if(isset($bLink['url'])): ?>
                    </a>
            <?php endif; ?>
        </li>
    @endforeach
</ol>
@endif