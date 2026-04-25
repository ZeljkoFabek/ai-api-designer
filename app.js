function sendToAI() {

    var xhr = new XMLHttpRequest();

    var name = document.getElementById("name").value;
    var position = document.getElementById("position").value;
    var experience = document.getElementById("experience").value;
    var skills = document.getElementById("skills").value;
    var languages = document.getElementById("languages").value;
    var education = document.getElementById("education").value;

    document.getElementById("result").innerText = "";

    xhr.addEventListener('readystatechange',function() {
        if (xhr.readyState == 4 && xhr.status == 200 ) {
            document.getElementById("result").innerText = xhr.responseText;
        }
    });

    var data = {
        name: name,
        position: position,
        experience: experience,
        skills: skills,
        languages: languages,
        education: education
    };

    xhr.open("POST", "/api/generate_cv.php", true);
    xhr.setRequestHeader("Content-Type", "application/json");

    xhr.send(JSON.stringify(data));
}