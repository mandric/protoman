
<html>

<head>
    <title>{{ block title }}ProtoAdmin{{ endblock title }}</title>
</head>

<body>
    {{ block breadcrumbs }}
        <a href="<?php print Controller::reverse('admin_home'); ?>">admin</a>
    {{ endblock breadcrumbs }}
    
    <hr />
    
    {{ block body }}Admin Body{{ endblock body }}
</body>

</html>
