<html>
<head></head>
<body>
<form action="" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="_token" value="{!! csrf_token() !!}">
    <label>Import file</label>
    <input type="file" name="file"></br>
    <button type="submit">Import</button>
</form>
</body>
</html>