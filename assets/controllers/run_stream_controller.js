import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['runId', 'canvas', 'generation', 'fitness', 'seed', 'status'];
    static values = { defaultRun: String };

    connect() {
        this.eventSource = null;
        if (this.hasDefaultRunValue && this.defaultRunValue !== '') {
            this.runIdTarget.value = this.defaultRunValue;
        }
        this.ctx = this.canvasTarget.getContext('2d');
        this.reset();
    }

    start() {
        this.stop();
        const runId = (this.runIdTarget.value || '').trim();
        if (runId === '') {
            this.setStatus('Missing run id');
            return;
        }

        const url = `/runs/${encodeURIComponent(runId)}/stream`;
        this.eventSource = new EventSource(url);

        this.eventSource.addEventListener('chunk', (event) => {
            const payload = JSON.parse(event.data);
            this.renderFrame(payload);
        });

        this.eventSource.addEventListener('done', () => {
            this.setStatus('Stream completed');
            this.stop();
        });

        this.eventSource.addEventListener('error', (event) => {
            const payload = event?.data ? JSON.parse(event.data) : null;
            this.setStatus(payload?.message || 'Stream error');
        });

        this.setStatus(`Streaming ${runId}`);
    }

    stop() {
        if (this.eventSource) {
            this.eventSource.close();
            this.eventSource = null;
        }
    }

    reset() {
        this.stop();
        const side = this.canvasTarget.width;
        this.ctx.fillStyle = '#000000';
        this.ctx.fillRect(0, 0, side, side);
        this.generationTarget.textContent = '-';
        this.fitnessTarget.textContent = '-';
        this.seedTarget.textContent = '-';
        this.setStatus('Idle');
    }

    renderFrame(frame) {
        const cells = frame.cells || [];
        const size = frame.size || cells.length || 1;
        const pixel = this.canvasTarget.width / size;

        for (let y = 0; y < size; y += 1) {
            for (let x = 0; x < size; x += 1) {
                const value = cells[y]?.[x] || 0;
                this.ctx.fillStyle = value ? '#ffffff' : '#000000';
                this.ctx.fillRect(x * pixel, y * pixel, pixel, pixel);
            }
        }

        const metadata = frame.metadata || {};
        this.generationTarget.textContent = `${metadata.generation ?? frame.frame ?? '-'}`;
        this.fitnessTarget.textContent = `${metadata.fitness ?? '-'}`;
        this.seedTarget.textContent = `${metadata.seed ?? '-'}`;
        this.setStatus(`Frame ${frame.frame ?? '-'}`);
    }

    setStatus(message) {
        this.statusTarget.textContent = message;
    }
}
