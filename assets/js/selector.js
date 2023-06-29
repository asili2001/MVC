(()=> {
    const selectors = document.querySelectorAll("[data-type='selector']");
    
    selectors.forEach(selector => {
        const reciver = document.querySelector(`[data-type='reciver'][data-id=${selector.getAttribute('data-id')}]`);
        let reciverItems = reciver.value.split(",");

        // parse items to int
        reciverItems = reciverItems.map(item => {
            let parsed = parseInt(item);

            console.log(parsed);

            if (!isNaN(parsed) && parsed !== "undefined") { return parsed }
        });

        reciverItems = reciverItems.filter(item => typeof item === "number");

        const selectSameName = selector.getAttribute("data-selector-type") === "sameName";
        let firstItemName = null;
        const selectItems = selector.querySelectorAll("[data-type='select-item']");
        const limitItems = selector.getAttribute("select-item");

        if (selectItems.length > parseInt(limitItems)) {
            return;
        }
        
        selectItems.forEach((item, index) => {
            item.addEventListener("click", (e) => {
                const itemName = item.getAttribute("selector-item-name");
                const isSelected = item.hasAttribute("selected");

                if (reciverItems.includes(index)) {
                    reciverItems.splice(reciverItems.indexOf(index), 1);
                    if (reciverItems.length < 1) {
                        firstItemName = null;
                    }
                    item.toggleAttribute("selected");
                } else {
                    if (selectSameName) {
                        if (firstItemName === null || firstItemName === itemName) {
                            reciverItems.push(index);
                            item.toggleAttribute("selected");
                            if (firstItemName === null) {
                                firstItemName = itemName;
                            }
                        }

                    } else {
                        reciverItems.push(index);
                        item.toggleAttribute("selected");
                    }
                }
                
                reciver.value = reciverItems.join(",");
                console.log(reciverItems);
            });
        });

    });

})();