<?php

include('../header/header.php');

?>

<body>
    <div class="form">
        <label for="summonerName">Summoner Name</label>
        <input type="text" id="summonerName" name="summonerName">
        <label for="matchCount">Number of matches</label>
        <input type="number" id="matchCount" name="matchCount" min="1" max="99">
        <input type="hidden" id="bot" name="bot">
        <button id="submit">Submit</button>
</div>

<div id="matchHistory">

</div> 

</body>

<script>

    const form = {
        summonerName: document.getElementById('summonerName'),
        matchCount: document.getElementById('matchCount'),
        bot: document.getElementById('bot'),
        submit: document.getElementById('submit'),
    };

    const matchHistory = document.getElementById('matchHistory');

    form.submit.addEventListener('click', () => {

        const request = new XMLHttpRequest();

        request.onload = () => {
            responseObject = null;

            try {
                responseObject = JSON.parse(request.responseText);
            } catch (e) {
                console.error('Could not pass JSON');
            }

            if (responseObject) {
                handleResponse(responseObject);
            }
        };

        requestData = `summonerName=${form.summonerName.value}&matchCount=${form.matchCount.value}`;

        request.open("POST", "tft-match-history.php");
        request.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        request.send(requestData);

        noCounter = 0;

    });

    function handleResponse (responseObject) {

        const img = document.createElement('img');
        const newSpan = document.createElement('span');
        const newDiv = document.createElement('div');

        console.log(responseObject);


            

           

        newDiv.classList.add('winrateData');
        // newDiv.innerHTML = `${responseObject.videoEmbed} <div class="channelDetails">
        // <img class="channelImage" src="${channelImage}"> 
        // <span class="channelName"><a href="https://www.youtube.com/channel/${responseObject.channelId}" target="_blank">${channelName}</a></span>
        // <span class="videoTitle">${responseObject.videoTitle}</span><span class="videoLive ${responseObject.videoLive}">${responseObject.videoLive}</span>
        // <span class="videoDesc">${responseObject.videoDesc}</span>
        // </div>`;

        matchHistory.appendChild(newDiv);


    };

</script>