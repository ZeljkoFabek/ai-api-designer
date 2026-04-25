
<script>
    var lastData = null;
</script>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>AI API Designer</title>
        <link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
        <link href="assets/fontawesome/css/all.min.css" rel="stylesheet">
    </head>

    <body class="p-4">
        <div class="container">
            <h2>AI API Designer</h2>

            <form method="post" onsubmit="sendToAI(event)">
                <textarea id="prompt" class="form-control mb-3" placeholder="Describe your system..."></textarea>
                <button type="submit" class="btn btn-primary">Generate</button>
            </form>
            
            <br>
            <p id="result"></p>

        </div>
    </body>

    <script>
        function generateSQLClick() {
            alert("CLICK WORKS");

            if (!lastData) {
                alert("No data!");
                return;
            }

            console.log(lastData);
            getSQL(lastData);
        }
    </script>    

    <script>
        function sendToAI(e) {

            e.preventDefault();

            var xhr = new XMLHttpRequest();

            var prompt = document.getElementById("prompt").value;
            var inerHTML = '<h4>Result</h4>' +
                           '<button class="btn btn-success mt-2" onclick="generateSQLClick()">Generate SQL</button>' +
                           '<pre id="sqlBox"></pre>';
    
            document.getElementById("result").innerText = "";

            xhr.addEventListener('readystatechange',function() {
                if (xhr.readyState == 4 && xhr.status == 200 ) {
                    document.getElementById("result").innerHTML = inerHTML;
                    lastData = xhr.responseText;
                    //console.log(lastData);
                }
            });

            var data = {
                prompt: prompt
            };

            xhr.open("POST", "api.php", true);
            xhr.setRequestHeader("Content-Type", "application/json; charset=utf-8");
            xhr.send(JSON.stringify(data));
        }
    </script>

    <script>
    function getSQL(data){
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "api_sql.php", true);
        xhr.setRequestHeader("Content-Type", "application/json; charset=utf-8");

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                var res = xhr.responseText;
                document.getElementById("sqlBox").innerText = res.sql;
            }
        };

        xhr.send(data);
    }
    </script>    

</html>