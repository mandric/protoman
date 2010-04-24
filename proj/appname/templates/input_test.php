
{{ extends admin/base.php }}

{{ block title }}
    An Awesome Page
{{ endblock title }}

{{ block body  }}
    <form method="POST">
        
        <?php print Response::$context['text']->form(); ?>
        
        <br /><br />
        
        <?php print Response::$context['char']->form(); ?>
        
        <br /><br />
        
        <?php print Response::$context['key']->form(); ?>
        
        <br /><br />
        
        <input type="submit" />
        
    </form>
{{ endblock body }}
