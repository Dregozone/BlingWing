const form = document.querySelector("main form")

// BEGIN prefill

;["restaurant", "occasion", "size", "food", "service", "value", "cleanliness"].map(name => {
    const el = form.querySelector(`select[name=${name}]`)
    const opts = el.querySelectorAll("option:not([hidden])")
    const opt = opts[~~(Math.random() * opts.length)]
    el.value = opt.value
})
form.querySelector("textarea[name=comment]").value = "Great place to eat!"

if (form.querySelector("input[name=name]")) {
    form.querySelector("input[name=name]").value = "John Doe"
    form.querySelector("input[name=email]").value = `john.x${Math.random().toString(36).substring(7)}@example.com`
}

// END prefill

form.addEventListener("submit", async ev => {
    ev.preventDefault()

    try
    {
        const data = {}
        let user

        for (const field of new FormData(form)) {
            data[field[0]] = field[1]
        }

        if (data.name) {
            const userResponse = await fetch("/api/users", {
                method: "POST",
                cache: "no-cache",
                headers : {
                    "Accept": "application/ld+json",
                    "Content-Type" : "application/json"
                },
                body: JSON.stringify({
                    name: data.name,
                    email: data.email
                })
            })

            user = await userResponse.json()

            if (userResponse.status >= 400 || user.violations) {
                throw new Error("Invalid user credentials")
            }
        }

        // make sure to pass selections as ints
        ["occasion", "size", "food", "service", "value", "cleanliness"].map(name => data[name] = parseInt(data[name]))

        const response = await fetch("/api/reviews", {
            method: "POST",
            cache: "no-cache",
            headers : {
                "Accept": "application/json",
                "Content-Type" : "application/json"
            },
            body: JSON.stringify(data)
        })

        if (response.status >= 400) {
            console.log(response);
            throw new Error("Request error.")
        }

        const result = await response.json()

        if (result.violations) {
            throw new Error(result.detail)
        }

        // if we have created an anonymous user, destroy temp session
        user && await fetch("/logout", { redirect: "manual" })

        form.classList.add("submitted")
    }
    catch (e)
    {
        alert(e.message)
    }

})
