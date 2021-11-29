<template>
    <table>
        <tr>
            <th>ShortURL</th>
            <th>URL</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <tr v-for="link in links" :key="link._id">
            <td>
                <router-link :to="{ name: 'Redirect', params: { urlKey:link.shortened } }" >{{ baseUrl + '/' + link.shortened }}</router-link>
            </td>
            <td>{{ link.original }}</td>
            <td>
                <p>{{ link.expired }}</p>
            </td>
            <td>
                <button>refresh</button>
                <button>modify</button>
                <button>delete</button>
            </td>
        </tr>
    </table>
</template>

<script>
export default {
    name: "LinksTable",
    data() {
        return {
            baseUrl: '',
            links: [
                {
                    _id: "61a260cc932ad76431b034c6",
                    original: "www.baidu.com",
                    shortened: "KmNL",
                    expiry: 1638638569,
                    expired: false,
                },
            ],
        };
    },
    mounted() {
        let url = window.location.origin.toLowerCase()
        if(url.startsWith('https://')){
            url = url.substring(8, url.length)
        } else if(url.startsWith('http://')){
            url = url.substring(7, url.length)
        }
        this.baseUrl = url
        // fetch actual data
    },
};
</script>

<style>
</style>