const domain = "../api/api"
async function get(api) {
    return await fetch(domain + api, {
        method: "GET",
    }).then(response => response.json())
        .catch(error => console.error('Error:', error));
}

async function post(api, data) {
    let formData = new FormData();
    for (let key in data) {
        formData.append(key, data[key]);
    }

    return await fetch(domain + api, {
        method: "POST",
        body: formData
    }).then(response => response.json())
        .catch(error => console.error('Error:', error));
}