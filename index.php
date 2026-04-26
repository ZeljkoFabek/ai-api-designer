<?php
    /**
     * PHP - Send generated SQL to local disk in user as schema.sql
     */
    if (isset($_POST['download_sql'])) {

        $sql = $_POST['sql_content'];

        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="schema.sql"');

        echo $sql;
        exit;
    }
?>

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
            <p id="lastTime"></p>
            <br>
            <p id="result"></p>
            <br>
        
        </div>

        <!-- modal frame and how long it takes for the process 
             to complete in minutes and seconds 
        -->
        <div id="loadingModal" style="
            display:none;
            position:fixed;
            top:0;
            left:0;
            width:100%;
            height:100%;
            background:rgba(0,0,0,0.7);
            z-index:9999;
            color:white;
            text-align:center;
            padding-top:200px;
            font-size:20px;">
            <div>
                <div class="spinner-border text-light"></div>
                <p>Processing request...</p>
                <p id="timer">0s</p>
            </div>
        </div>

    </body>
    <!-- modal frame End  -->

    <script>
        var lastData = null;
        var timerInterval = null;
        var seconds = 0;        
        
        /** 
         * Generating SQL from JSON information 
         * sent by LM Studio in response.
        */  
        function generateSQLClick() {
            if (!lastData) {
                alert("No data!");
                return;
            }
            getSQL(lastData);
        }

        /** 
         * sending a query or prompt to LM Studio 
         * so that it sends a response in JSON format.
        */        
        function sendToAI(e) {

            e.preventDefault();

            var xhr = new XMLHttpRequest();

            var prompt = document.getElementById("prompt").value;
            var inerHTML = '<h4>Result</h4>' +
                           '<button class="btn btn-success mt-2" onclick="generateSQLClick()">Generate SQL</button>';
    
            document.getElementById("result").innerText = "";

            xhr.addEventListener('readystatechange',function() {
                if (xhr.readyState == 4 && xhr.status == 200 ) {

                    stopLoading();
                    
                    document.getElementById("result").innerHTML = inerHTML;
                    
                    var last = getCookie("lastRequestTime");

                    if (last) {
                        var min = Math.floor(last / 60);
                        var sec = last % 60;                        
                        document.getElementById("lastTime").innerText = "Last request took: " + min + "m " + sec + "s";
                    }

                    lastData = xhr.responseText;
                    var data = JSON.stringify(lastData);
                    console.log(lastData);
                    console.log(data);
                    //renderResult(data);
                }
            });

            var data = {
                prompt: prompt
            };

            startLoading();

            xhr.open("POST", "api.php", true);
            xhr.setRequestHeader("Content-Type", "application/json; charset=utf-8");
            xhr.send(JSON.stringify(data));
        }

        /**
         * generating SQL from JSON format
         */
        function getSQL(data){

            var xhr = new XMLHttpRequest();
            
            xhr.open("POST", "api_sql.php", true);
            xhr.setRequestHeader("Content-Type", "application/json; charset=utf-8");

            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {

                    var res = JSON.parse(xhr.responseText);
                    var SQL = res.sql;

                    var HTML = '';
                    
                    HTML += '<form method="POST">';
                    HTML += '<input type="hidden" name="download_sql" value="1">';
                    HTML += '<input type="hidden" name="sql_content" value="' + SQL + '">';
                    HTML += '<form method="POST">';
                    
                    console.log(SQL);
                    document.getElementById("result").innerHTML = HTML;
                }
            };

            xhr.send(data);
        }

        /**
         * creating EndPoints or REST API routes 
         * from the JSON format sent by LM Studio in response.
         */
        function renderResult(data) {

            var html = "<h4>Description</h4>";
            html += "<p>" + data.description + "</p>";

            html += "<h4>API Endpoints</h4>";

            for (var i = 0; i < data.api_endpoints.length; i++) {

                var api = data.api_endpoints[i];

                html += '<div class="card mb-2 p-2">';
                html += '<b>' + api.method + '</b> ' + api.path + '<br>';
                html += '<small>' + api.description + '</small>';

                if (api.example_request_body) {
                    html += '<pre>' + JSON.stringify(api.example_request_body, null, 2) + '</pre>';
                }

                if (api.example_response) {
                    html += '<pre>' + JSON.stringify(api.example_response, null, 2) + '</pre>';
                }

                html += '</div>';
            }

            html += '<button class="btn btn-success mt-2" onclick="generateSQLClick()">Generate SQL</button>';
            html += '<pre id="sqlBox"></pre>';

            document.getElementById("result").innerHTML = html;
        }
        
        /**
         * modal frame show
         */
        function startLoading() {

            seconds = 0;

            document.getElementById("loadingModal").style.display = "block";

            timerInterval = setInterval(function () {

                seconds++;

                var min = Math.floor(seconds / 60);
                var sec = seconds % 60;

                document.getElementById("timer").innerText = min + "m " + sec + "s";

            }, 1000);
        }

        /**
         * modal frame hide or close
         */        
        function stopLoading() {

            clearInterval(timerInterval);

            document.getElementById("loadingModal").style.display = "none";

            // save time in cookie (e.g. 1 day)
            document.cookie = "lastRequestTime=" + seconds + "; path=/; max-age=86400";
        }
        
        /**
         * reading Cookie information how long process 
             to complete in minutes and seconds
         */        
        function getCookie(name) {

            var value = "; " + document.cookie;
            var parts = value.split("; " + name + "=");

            if (parts.length == 2) return parts.pop().split(";").shift();
        }        

    </script>    

</html>