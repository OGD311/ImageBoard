export function add_to_favourites(post_id, user_id) {
    const url = '/core/favourites/favourites-toggle.php';

    const formData = new FormData();
    formData.append('post_id', post_id);
    formData.append('user_id', user_id);
    formData.append('action', 'add');

    fetch(url, {
        method: 'POST',
        body: formData,
    }).then(response => response.json())
        .then(data => {
            if (data.success) {

            const favourite = document.getElementById(`addFavourite ${post_id}`);
            console.log(favourite);
            const image = favourite.querySelector('img');
            image.src = '/static/svg/heart-fill-icon.svg';
            favourite.id = `removeFavourite ${post_id}`;
            favourite.setAttribute('onclick', `remove_from_favourites(${post_id}, ${user_id})`);
        }
    });
 
}


export function remove_from_favourites(post_id, user_id) {
    const url = '/core/favourites/favourites-toggle.php';

    const formData = new FormData();
    formData.append('post_id', post_id);
    formData.append('user_id', user_id);
    formData.append('action', 'remove');

    fetch(url, {
        method: 'POST',
        body: formData,
    }).then(response => response.json())
    .then(data => {
        if (data.success) {


            const favourite = document.getElementById(`removeFavourite ${post_id}`);
            console.log(favourite);
            const image = favourite.querySelector('img');
            image.src = '/static/svg/heart-empty-icon.svg';
            favourite.id = `addFavourite ${post_id}`;
            favourite.setAttribute('onclick', `add_to_favourites(${post_id}, ${user_id})`);
    
        }
    });
}


window.add_to_favourites = add_to_favourites;
 
window.remove_from_favourites = remove_from_favourites;
