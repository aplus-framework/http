<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CSP Test</title>
    <style>
        body {
            background: cyan;
        }
    </style>
    <style>
        body {
            color: blue;
        }
        h1 {color: yellow}
    </style>
</head>
<body>
<script>
    console.log('Hello!');
</script>
<script foo="bar" bar>
    console.log('Bye.');
    // it is a comment
</script>
</body>
</html>
