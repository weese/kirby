<template>
  <section class="k-info-section">
    <k-headline class="k-info-section-headline">{{ headline }}</k-headline>
    <k-box :theme="theme">
      <k-text v-html="text" />
    </k-box>
  </section>
</template>

<script>
import SectionMixin from "@/mixins/section/section.js";

export default {
  mixins: [SectionMixin],
  props: {
    headline: String,
    text: String,
    theme: String
  }
};
</script>
<style>

.k-info-section-headline {
  margin-bottom: .5rem;
}

</style>

