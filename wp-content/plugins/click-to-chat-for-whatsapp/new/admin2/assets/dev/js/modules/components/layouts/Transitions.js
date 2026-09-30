/**
 * Trigger a smooth collapse on an element using a CSS `max-height` transition.
 *
 * The forced reflow between the explicit-height write and the `0` write is
 * REQUIRED — without it the browser batches both writes as a single update and
 * the transition silently doesn't fire.
 *
 * @param {HTMLElement} el
 */
export const collapseWithTransition = ( el ) => {
	el.style.maxHeight = el.scrollHeight + 'px';
	void el.offsetHeight;
	requestAnimationFrame( () => {
		el.style.maxHeight = '0';
	} );
};

/**
 * Trigger a smooth expand on an element using a CSS `max-height` transition,
 * then lift the cap so inner content reflows freely. `isStillActive` lets the
 * caller skip the final cap-lift if the user has since collapsed the element.
 *
 * Uses `{ once: true }` so the listener self-cleans even when the transition
 * is interrupted (collapse-mid-expand).
 *
 * @param {HTMLElement} el
 * @param {() => boolean} isStillActive
 */
export const expandWithTransition = ( el, isStillActive ) => {
	el.style.maxHeight = el.scrollHeight + 'px';
	el.addEventListener( 'transitionend', () => {
		if ( isStillActive() ) {
			el.style.maxHeight = 'none';
		}
	}, { once: true } );
};
