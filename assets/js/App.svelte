<script>
    import { onMount } from 'svelte';
    import FullCalendar from 'svelte-fullcalendar';
    import daygridPlugin from '@fullcalendar/daygrid';
    import apiFetch from '@wordpress/api-fetch';
    import { find, forEach, get } from 'lodash';
    import moment from 'moment';

    import Loader from './components/Loader.svelte';

    const rootURL = window.bmApp.rest_url;

    let loading = true;
    let options = {
        initialView: 'dayGridMonth',
        plugins: [daygridPlugin],
        events: [],
    };
    let employees = [];

    apiFetch.use( apiFetch.createRootURLMiddleware( rootURL ) );

    function addEvent( leave, employeeObj ) {
        const leaveDetails = get( leave, 'cmb2._bm_leave_details_box' );
        const { events } = options;
        const firstName = get( employeeObj, 'cmb2._bm_employee_details_box._bm_employee_first_name' );
        const lastName = get( employeeObj, 'cmb2._bm_employee_details_box._bm_employee_last_name' );

        const item = {
            title: `${firstName} ${lastName}`,
            start: moment( leaveDetails._bm_leave_start, 'X' ).format( 'YYYY-MM-DD' ),
            end: moment( leaveDetails._bm_leave_end, 'X' ).format( 'YYYY-MM-DD' ),
            url: `post.php?post=${leave.id}&action=edit`,
        };

        employees = [ ...employees, employeeObj ];

        const calendarEvents = [
            ...events,
            item
        ];

        options = {
            ...options,
            events: calendarEvents,
        };
    }

	onMount(async () => {
		const fetchLeaves = await apiFetch( { path: 'wp/v2/bm-leave?per_page=100' } )
        .then( async (leavesObj) => {
            for(leave of leavesObj) {
                const leaveDetails = get( leave, 'cmb2._bm_leave_details_box' );
                const employee = leaveDetails._bm_leave_employee;

                if ( leaveDetails._bm_leave_status !== 'Approved' ) {
                    continue;
                }

                let fetchEmployee = find( employees, { id: employee } );
                if ( fetchEmployee ) {
                    addEvent( leave, fetchEmployee );
                } else {
                    fetchEmployee = await apiFetch( { path: `wp/v2/bm-employee/${employee}` } );
                    addEvent( leave, fetchEmployee );
                }
            }

            loading = false;
        });
	})
</script>

{#if loading}
    <Loader />
{/if}
<FullCalendar {options} />