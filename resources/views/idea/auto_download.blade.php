<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Auto Download</title>
</head>
<body>
    <a id="csvLink" href="{{ $csvUrl }}" download style="display:none;"></a>
    <a id="zipLink" href="{{ $zipUrl }}" download style="display:none;"></a>

    <script>
        document.getElementById('csvLink').click();
        setTimeout(function() {
            document.getElementById('zipLink').click();
        }, 1000); // Wait 1 second before downloading second file
    </script>
</body>
</html>
