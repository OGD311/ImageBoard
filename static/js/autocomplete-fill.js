export function autocomplete(element) {
    element.addEventListener("keyup", function () {
        let inputVal = element.value;
        if (inputVal === "" || inputVal.endsWith(" ")) {
            element.parentElement.querySelector("#autocompleteBox").style.display = "none";
        } else {
            let search = inputVal.trim().split(" ");
            let lastWord = search.pop();
            
            let xhr = new XMLHttpRequest();
            xhr.open("POST", "/core/autocomplete-search.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    element.parentElement.querySelector("#autocompleteBox").style.display = "block";
                    element.parentElement.querySelector("#autocompleteBox").innerHTML = xhr.responseText;
                    element.style.background = "#FFF";
                }
            };
            xhr.send('word=' + lastWord + '&search=' + search.join(" "));
        }
    });
}

// Attach the function to the window object
window.autocomplete = autocomplete;
