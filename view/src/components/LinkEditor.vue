<template>
    <div id="warp" @click.self="closeModal">
        <div class="modal">
            <form class="modifyURL" @submit.prevent="updateURL">
                <label>URL:</label>
                <input type="text" v-model="newUrl" required />
                <div class="submit">
                    <button>Save</button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            newUrl: this.oldUrl,
            id:""
        };
    },
    methods: {
        setup(data) {
            this.newUrl = data.link,
            this.id = data.id
        },
        closeModal() {
            this.$emit("closeModal");
        },
        updateURL() {
            let url = this.newUrl.toLowerCase();
            if (!url.length) {
                this.errorMsg="input something!!"
            } else {
                if (!url.startsWith("https://") && !url.startsWith("http://")) {
                    this.newUrl = "http://" + this.newUrl;
                }
                this.$emit("updateURL", {
                    newUrl: this.newUrl,
                    id: this.id,
                    msg: "Updated link to " + this.newUrl,
                });
            }
        },
    },
};
</script>

<style>
/* Credit https://github.com/iamshaunjp/Vue-3-Firebase/blob/lesson-26/modal-project/src/components/Modal.vue */
#warp {
    top: 0;
    position: fixed;
    background: rgba(0, 0, 0, 0.664);
    width: 100%;
    height: 100%;
}
#warp .modal {
    max-width: 420px;
    margin: 10% auto;
    background: white;
    text-align: left;
    padding: 40px;
    border-radius: 2rem;
}

#warp .modal .modifyURL label {
    color: #aaa;
    display: inline-block;
    margin: 25px 0 15px;
    font-size: 0.8em;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: bold;
}

#warp .modal .modifyURL button {
    background: #0b6dff;
    border: 0;
    padding: 10px 20px;
    margin-top: 20px;
    color: white;
    border-radius: 20px;
}

#warp .modal .modifyURL button:hover {
    background: #217aff;
    border: 0;
    padding: 10px 20px;
    margin-top: 20px;
    color: white;
    border-radius: 20px;
}
#warp .modal .modifyURL .submit {
    text-align: center;
}

#warp .modal .modifyURL input {
    display: block;
    padding: 10px 6px;
    width: 100%;
    box-sizing: border-box;
    border: none;
    border-bottom: 1px solid #ddd;
    color: #555;
}
</style>