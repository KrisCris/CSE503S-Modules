const BaseURL = process.env.VUE_APP_SERVER_URL

function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
}

export const GET = async (api) => {
    return await fetch(BaseURL + api, {
        method: "GET",
        headers: {
            'X-CSRF-TOKEN': getCookie('csrf_access_token'),
        },
        credentials: 'include'
    }).then(response => response.json())
        .catch(error => console.error('Error:', error));
}

export const POST = async (api, data) => {
    let formData = new FormData();
    for (let key in data) {
        formData.append(key, data[key]);
    }

    return await fetch(BaseURL + api, {
        method: "POST",
        body: formData,
        headers: {
            'X-CSRF-TOKEN': getCookie('csrf_access_token'),
        },
        credentials: 'include'
    }).then(response => response.json())
        .catch(error => console.error('Error:', error));
}

export const PUT = async (api, data) => {
    let formData = new FormData();
    for (let key in data) {
        formData.append(key, data[key]);
    }

    return await fetch(BaseURL + api, {
        method: "PUT",
        body: formData,
        headers: {
            'X-CSRF-TOKEN': getCookie('csrf_access_token'),
        },
        credentials: 'include'
    }).then(response => response.json())
        .catch(error => console.error('Error:', error));
}

export const PATCH = async (api, data) => {
    let formData = new FormData();
    for (let key in data) {
        formData.append(key, data[key]);
    }

    return await fetch(BaseURL + api, {
        method: "PATCH",
        body: formData,
        headers: {
            'X-CSRF-TOKEN': getCookie('csrf_access_token'),
        },
        credentials: 'include'
    }).then(response => response.json())
        .catch(error => console.error('Error:', error));
}

export const DELETE = async (api, data) => {
    let formData = new FormData();
    for (let key in data) {
        formData.append(key, data[key]);
    }

    return await fetch(BaseURL + api, {
        method: "DELETE",
        body: formData,
        headers: {
            'X-CSRF-TOKEN': getCookie('csrf_access_token'),
        },
        credentials: 'include'
    }).then(response => response.json())
        .catch(error => console.error('Error:', error));
}