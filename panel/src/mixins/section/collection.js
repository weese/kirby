export default {
  inheritAttrs: false,
  props: {
    column: String,
    empty: String,
    headline: String,
    help: String,
    layout: String,
    link: String,
    name: String,
    parent: String,
    required: Boolean,
    size: String,
  },
  data() {
    return {
      data: [],
      error: null,
      isLoading: false,
      options: {
        add: false,
        max: null,
        min: null,
        sortable: null
      },
      pagination: {
        page: null
      }
    };
  },
  computed: {
    isInvalid() {
      if (this.options.min && this.data.length < this.options.min) {
        return true;
      }

      if (this.options.max && this.data.length > this.options.max) {
        return true;
      }

      return false;
    },
    language() {
      return this.$store.state.languages.current;
    },
    paginationId() {
      return "kirby$pagination$" + this.parent + "/" + this.name;
    }
  },
  watch: {
    language() {
      this.reload();
    }
  },
  methods: {
    fill(response) {
      this.isLoading  = false;
      this.options    = response.options;
      this.pagination = response.pagination;
      this.data       = this.items(response.data);
    },
    items(data) {
      return data;
    },
    load(reload) {
      if (!reload) {
        this.isLoading = true;
      }

      if (this.pagination.page === null) {
        this.pagination.page = localStorage.getItem(this.paginationId) || 1;
      }

      this.$api
        .get(this.parent + "/sections/" + this.name, {
          page: this.pagination.page
        })
        .then(response => {
          this.fill(response);
        })
        .catch(error => {
          this.isLoading = false;
          this.error = error.message;
        });
    },
    paginate(pagination) {
      localStorage.setItem(this.paginationId, pagination.page);
      this.pagination = pagination;
      this.reload();
    },
    reload() {
      this.load(true);
    }
  }
};
