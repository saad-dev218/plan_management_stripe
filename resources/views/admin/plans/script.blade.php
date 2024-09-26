<script>
    document.querySelectorAll('.delete-plan').forEach(button => {
        button.addEventListener('click', function() {
            const planId = this.getAttribute('data-id');
            const form = document.getElementById(`delete-plan-form-${planId}`);

            swal({
                title: "Are you sure?",
                text: "Once deleted, you will not be able to recover this plan!",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
            .then((willDelete) => {
                if (willDelete) {
                    form.submit(); // Submit the form to delete the plan
                } else {
                    swal("Your plan is safe!");
                }
            });
        });
    });
</script>
