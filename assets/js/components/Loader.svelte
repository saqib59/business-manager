<script>
	import { Circle } from 'svelte-loading-spinners';
</script>

<div class="overlay">
    <Circle size="60" color="#2271b1" unit="px" duration="1s" />
</div>

<style>
    .overlay {
      position: absolute;
      top: 0;
      right: 0;
      bottom: 0;
      left: 0;
      z-index: 10001;
      display: flex;
      align-items: top;
      justify-content: center;
      background: hsla(0, 0%, 100%, .9);
      padding: 50px 0px;
    }
  </style>