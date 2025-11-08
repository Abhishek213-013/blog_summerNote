<!DOCTYPE html>
<html>
<head>
    <title>Test Image Upload</title>
</head>
<body>
    <h1>Test Image Upload</h1>
    <form action="/test-image-upload" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="file" name="test_image" accept="image/*">
        <button type="submit">Upload Test Image</button>
    </form>
</body>
</html>