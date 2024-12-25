export function add_to_favourites(post_id, user_id) {
    const url = '/core/favourites/favourites-toggle.php';

    const formData = new FormData();
    formData.append('post_id', post_id);
    formData.append('user_id', user_id);
    formData.append('action', 'add');

    fetch(url, {
        method: 'POST',
        body: formData,
    })

    location.reload();
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
    })

    location.reload();
}


window.add_to_favourites = add_to_favourites;
 
window.remove_from_favourites = remove_from_favourites;
