<template>
  <section class="k-fields-section">
    <k-form
      :fields="form"
      :validate="true"
      :value="values"
      :disabled="$store.state.content.status.lock !== null"
      @input="input"
      @submit="onSubmit"
    />
  </section>
</template>

<script>
import SectionMixin from "@/mixins/section/section.js";

export default {
  mixins: [SectionMixin],
  inheritAttrs: false,
  props: {
    fields: Object,
  },
  computed: {
    form() {
      let form = {};

      Object.keys(this.fields).forEach(name => {
        form[name] = {
          ...this.fields[name],
          section: this.name,
          endpoints: {
            field: this.parent + "/fields/" + name,
            section: this.parent + "/sections/" + this.name,
            model: this.parent
          }
        };
      });

      return form;
    },
    language() {
      return this.$store.state.languages.current;
    },
    values() {
      return this.$store.getters["content/values"]();
    }
  },
  methods: {
    input(values, field, fieldName) {
      this.$store.dispatch("content/update", [
        fieldName,
        values[fieldName]
      ]);
    },
    onSubmit($event) {
      this.$events.$emit("keydown.cmd.s", $event);
    }
  }
};
</script>

<style>
.k-fields-issue-headline {
  margin-bottom: 0.5rem;
}
.k-fields-section input[type="submit"] {
  display: none;
}

[data-locked] .k-fields-section {
  opacity: .2;
  pointer-events: none;
}
</style>
