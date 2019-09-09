Vue.component ("trca-docs", {
    template: `
    <main class="trca-docs">
        <slot></slot>
    </main>`
})

Vue.component ("header-title", {
    props: {
        title: String
    },
    template: `
    <div class="trca-docs-header">
        {{ title }}
        <slot></slot>
    </div>`
})

Vue.component ("header-menu", {
    template:`
    <div class="trca-docs-header-menu" @click="showHeaderMenu">
        <div class="trca-docs-header-menu-icon">
            <div class="material-icons title">
                more_vert
            </div>
        </div>
        <ul id="trca-docs-header-menu-items">
            <slot></slot>
        </ul>
    </div>`,
    methods: {
        showHeaderMenu: function() {
            $('#trca-docs-header-menu-items').toggle();
            $(document).on('click', function (e) {
                if (!$(e.target).closest(".title").length) {
                    $('#trca-docs-header-menu-items').hide();
                }
                e.stopPropagation();
            });
        }
    }
})

Vue.component("header-menu-button", {
    props: {
        message: String,
        icon: String,
        id: Array
    },
    template: `
    <div class="trca-docs-header-button" @click="buttonClick">
        <div class="material-icons">{{ icon }}</div>
        {{ message }}
    </div>`,
    methods: {
        buttonClick: function() {
            this.$emit("button-click", this.id);
        }
    }
})

Vue.component ("docs-content", {
    template: `
    <div class="trca-docs-content">
        <slot></slot>
    </div>`
})

Vue.component ("docs-items", {
    template: `
    <div class="trca-docs-content-items">
        <slot></slot>
    </div>
    `
})

Vue.component ("doc-name", {
    props:{
        name: String,
        description: String,
        icon: String,
        color: String,
    },
    template: `
    <div class="trca-docs-content-item-left">
        <div class="material-icons" :style="color">
            {{ icon }}
        </div>
        <div class="trca-docs-content-doc">
            <div class="trca-docs-content-doc-name" :title="name">
                {{ name }}
            </div>
            <div class="trca-docs-content-doc-description" v-html="description">
            </div>
        </div>
    </div>`,
})

Vue.component ("doc-buttons", {
    template: `
    <div class="trca-docs-content-item-right">
        <slot></slot>
    </div>`
})

Vue.component ("doc-button", {
    props: {
        id: Number,
        title: String,
        icon: String
    },
    template: `
    <div class="trca-docs-content-button" :title="title" @click="buttonClick">
        <i class="material-icons">
            {{ icon }}
        </i>
    </div>`,
    methods : {
        buttonClick: function() {
            let idAr = new Array();
            idAr = [this.id];
            this.$emit('button-click', idAr);
        }
    }
})

