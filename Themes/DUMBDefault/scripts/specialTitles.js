document.addEventListener('DOMContentLoaded', () => {
    'use strict';
    if(window.location.href.indexOf('/index.php/topic') != -1) {
        const audio_group_52 = new Audio("https://deltaruneboards.net/assets/audio/yourtakingtoolong.mp3");
        const audio_group_59 = new Audio("https://deltaruneboards.net/assets/audio/tropic.mp3");
        const url_group_34_35 = "https://api.weatherapi.com/v1/current.json?key=822cd91afbca4a6e835211020261309&q=auto:ip&aqi=no";

        var arr = document.querySelectorAll('img.group-32'); // Youthful - 32
        arr.forEach(group32);
        arr = document.querySelectorAll('img.group-34'); // Forecaster C, F - 34, 35
        arr.forEach(group34);
        arr = document.querySelectorAll('img.group-35'); // Forecaster C, F - 34, 35
        arr.forEach(group35);
        arr = document.querySelectorAll('img.group-50'); // Roommate - 50
        arr.forEach(group50);
        arr = document.querySelectorAll('img.group-52'); // Long - 52
        arr.forEach(group52);
        arr = document.querySelectorAll('img.group-59'); // South - 59
        arr.forEach(group59);

        function group32(title) {
            title.src = "https://deltaruneboards.net/assets/titles/theyouthfuldays.png";
            title.parentElement.style = "margin-bottom:1em;margin-left:-5em;";
        }

        function group34(title) {
            var txt = document.createElement("div");
            txt.textContent = "Click!";
            txt.classList.add('group-34-35-text');
            txt.addEventListener("click", () => {
                fetch(url_group_34_35).then(res => res.json())
                .then(data =>
                     replaceText(txt, data.current.temp_c, " °C"))
                .catch(err => console.log(err));
            });
            title.parentElement.insertBefore(txt, title);

        }

        function group35(title) {
            var txt = document.createElement("div");
            txt.textContent = "Click!";
            txt.classList.add('group-34-35-text');
            txt.addEventListener("click", () => {
                fetch(url_group_34_35).then(res => res.json())
                .then(data =>
                     replaceText(txt, data.current.temp_f, " °F"))
                .catch(err => console.log(err));
            });
            title.parentElement.insertBefore(txt, title);
        }

        function replaceText(txt, data, temp) {
            txt.textContent = data + temp;
        }

        function group50(title) {
            var btn = document.createElement("button");
            btn.classList.add('group-50-playbutton');
            btn.addEventListener("click", () => {
                var track = Math.floor(Math.random() * 38) + 1;
                new Audio("https://deltaruneboards.net/assets/audio/flowery_lines/"+track+".mp3").play(); // This feels illegal
            });
            title.parentElement.append(btn);
            title.parentElement.innerHTML = title.parentElement.innerHTML.replace(/\bLV\d{1,}\b/, "LV99");
        }

        function group52(title) {
            title.src = "https://deltaruneboards.net/assets/titles/takingtoolong.gif";
            var btn = document.createElement("button");
            btn.classList.add('group-52-playbutton');
            btn.addEventListener("click", () => {
                audio_group_52.play();
            });
            title.parentElement.append(btn);
        }

        function group59(title) {
            var txt = document.createElement("div");
            txt.classList.add('group-59-text');
            title.parentElement.insertBefore(txt, title);
            title.addEventListener("click", () => {
                title.src = "https://deltaruneboards.net/Themes/DUMBDefault/images/blank.png";
                title.classList.add('oflove');
                audio_group_59.play();
                audio_group_59.addEventListener('ended', () => {
                    title.classList.remove('oflove');
                    title.src = "https://deltaruneboards.net/Themes/default/images/membericons/tropic.gif";
                });
            });
        }
    }
});