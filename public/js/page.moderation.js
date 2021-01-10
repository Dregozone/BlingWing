const forms = document.querySelectorAll("main form")

forms.forEach(form => {
    form.addEventListener("submit", async ev => {
        ev.preventDefault()

        const action = ev.submitter ? ev.submitter.name : "skip"

        if (action !== "skip") {
            const data = {}

            for (const field of new FormData(form)) {
                data[field[0]] = field[1]
            }

            // make sure to pass selections as ints
            ["occasion", "size", "food", "service", "value", "cleanliness"].map(name => data[name] = parseInt(data[name]))

            const response = await fetch(`/api/reviews/${data.id}`, {
                method: "PUT",
                cache: "no-cache",
                headers : {
                    "Accept": "application/json",
                    "Content-Type" : "application/json"
                },
                body: JSON.stringify({
                    restaurant : data.restaurant,
                    food : data.food,
                    service : data.service,
                    cleanliness : data.cleanliness,
                    value : data.value,
                    occasion : data.occasion,
                    size : data.size,
                    comment : data.comment,
                    status : action === "delete" ? 5 : 3
                })
            })

            const result = await response.json()
        }

        form.classList.add("moderated");
    })
})
