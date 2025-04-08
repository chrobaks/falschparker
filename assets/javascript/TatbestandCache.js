/**
 * Class TatbestandCache
 */
export class TatbestandCache
{
    constructor(config)
    {
        this.container = document.getElementById(config.selector.tatbestandWrapper);
        this.selectTatbestand = document.getElementById(config.selector.tatbestandSelect);
        this.countTatbestand = document.getElementById(config.selector.tatbestandCount);
        this.inputCounts = document.getElementById(config.selector.inputCounts);
        this.count = document.getElementById(config.selector.count);
        this.btnCache = document.getElementById(config.selector.btnCache);

        this.btnCache.addEventListener('click', () => {
            this.setCache();
        });
    }

    /**
     *
     * @returns void
     */
    toggleContainerDisplay ()
    {
        this.container.classList.toggle('active');
    }

    /**
     *
     * @param updateCount number
     *
     * @returns void
     */
    renderBox (updateCount)
    {
        const tatbestand = this.getTatbestand();

        if (updateCount === 0) {
            const box = document.createElement('div');
            box.classList.add("col-12", "col-lg-4", "tatbestand-box");
            box.setAttribute('data-tatbestand-id', tatbestand.id);

            const inputText = document.createElement('input');
            inputText.classList.add("tatbestand-text");
            inputText.value = tatbestand.text;
            inputText.readonly = true;

            const inputCount = document.createElement('input');
            inputCount.classList.add("tatbestand-count");
            inputCount.value = tatbestand.count;
            inputCount.readonly = true;

            const btn = document.createElement('button');
            btn.innerHTML = '<i class="bi bi-trash"></i>';
            btn.setAttribute('data-tatbestand-id', tatbestand.id);

            box.append(inputText, inputCount, btn);
            this.container.append(box);

            btn.addEventListener('click', () => {
                if (btn?.dataset?.tatbestandId) {
                    box.remove();
                    this.deleteFromCache(btn.dataset.tatbestandId);
                }
            });
        } else {
            const box = document.querySelector(`div[data-tatbestand-id="${tatbestand.id}"]`);
            box.querySelector('.tatbestand-count').value = updateCount;
        }
    }

    /**
     *
     * @param id string
     *
     * @returns void
     */
    deleteFromCache (id)
    {
        const counts = this.inputCounts.value.split(';');
        const newCounts = [];
        [...counts].map(item => {
            let [countVal, tatbestandId] = item.split(',');
            if (tatbestandId !== id) {
                newCounts.push(item);
            }
        });
        this.inputCounts.value = newCounts.join(';');
    }

    /**
     *
     * @returns void
     */
    setCache () {
        let updateCount = 0;
        if (/^[\d],/.test(this.inputCounts.value)) {
            const counts = this.inputCounts.value.split(';');
            const newCounts = [];
            [...counts].map(item => {
                let [countVal, tatbestandId] = item.split(',');
                if (tatbestandId === this.selectTatbestand.value) {
                    updateCount = countVal * 1 + this.count.value * 1;
                    item = updateCount  + ',' + tatbestandId;
                }
                newCounts.push(item);
            });
            if (updateCount === 0) {
                newCounts.push(this.count.value + ',' + this.selectTatbestand.value);
            }
            this.inputCounts.value = newCounts.join(';');
        } else {
            this.inputCounts.value = this.count.value + ',' + this.selectTatbestand.value;
        }
        this.renderBox(updateCount);
    }

    /**
     *
     * @returns {{text, id: *, count: *}}
     */
    getTatbestand ()
    {
        return {
            text: this.selectTatbestand.options[this.selectTatbestand.selectedIndex].text,
            id: this.selectTatbestand.value,
            count: this.countTatbestand.value,
        };
    }
}
window.TatbestandCache = TatbestandCache;