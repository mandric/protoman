
<html>

<head>
    <title>{{ block title }}ProtoMan!{{ endblock title }}</title>
</head>

<body>
    {{ block breadcrumbs }}Home{{ endblock breadcrumbs }}
    
    <br /><br />
    
    {{ block body }}
        Default body.
        
        {{ block internal-test }}
            Testing internal block.
        {{ endblock internal-test }}
    {{ endblock body }}
</body>

</html>
