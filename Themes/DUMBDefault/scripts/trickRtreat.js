document.addEventListener('DOMContentLoaded', () => {
    let click = document.getElementById("trickCandy");

    click.style.top = getRandom(20, window.innerHeight) + "px";
    click.style.left = getRandom(20, window.innerWidth) + "px";
    click.style.position = "absolute";

    document.body.insertBefore(click, document.getElementById("wrapper"));
    
    console.log("spawned candy");
});

function getRandom(min, max) {
    return Math.random() * (max - min) + min;
}
