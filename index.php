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
            
            <button 
                class="btn btn-outline-primary mb-3"
                onclick="generateExamples()"
                id="toggleBtn">
                Show Examples
            </button>

            <br>
            <div id="examples"></div>
            <p id="lastTime"></p>
            <p id="sqlBox"></p>
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

                        deleteCookie("lastRequestTime");
                        last = 0;
                        document.getElementById("lastTime").innerText = "Last request took: " + min + "m " + sec + "s";
                    }

                    lastData = xhr.responseText;                    
                    renderResult(JSON.parse(lastData));
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

                    document.getElementById("result").innerHTML = '';

                    var res = JSON.parse(xhr.responseText);
                    var html = '';
                    
                    html += '<form method="POST">';
                    html += '<input type="hidden" name="download_sql" value="1">';
                    html += '<input type="hidden" name="sql_content" value="' + res.sql + '">';
                    html += '<button class="btn btn-success mt-2">Download SQL</button>';
                    
                    document.getElementById("sqlBox").innerHTML = '<br><pre>' + res.sql + '</pre><br>';
                    document.getElementById("result").innerHTML = '<br>' + html + '<br>';
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
        
        function deleteCookie(name) {
            document.cookie = name + "=; path=/; max-age=0";
        }
        
        function setPrompt(el) {
            document.getElementById("prompt").value = el.innerText;

            // Hide List Of Examples
            document.getElementById("examples").style.display = "none";
            document.getElementById("toggleBtn").innerText = "Show Examples";            
        }
        
        function generateExamples() {
                var prompts = [
                "E-commerce system for products, orders and users",
                "User management system with roles and permissions",
                "System for managing events, tickets and attendees",
                "Hospital system for patients, doctors and appointments",
                "System for storing AI prompts and responses history",
                "Smart energy system for tracking devices and energy consumption",
                "Banking system with accounts and transactions",
                "Inventory management system for products and stock tracking",
                "Reservation system for booking appointments"
            ];

            var html = "";

            for (var i = 0; i < prompts.length; i++) {
                html += '<button class="btn btn-outline-primary btn-sm m-1" onclick="setPrompt(this)" title="Use this example and generate API instantly">' + prompts[i] + '</button>';
            }

            document.getElementById("examples").innerHTML = html;
            
            var btn = document.getElementById("toggleBtn");
            var box = document.getElementById("examples");

            if (box.style.display === "none") {
                box.style.display = "block";
                btn.innerText = "Hide Examples";
            } else {
                box.style.display = "none";
                btn.innerText = "Show Examples";
            }             
        }

    </script>    

</html>