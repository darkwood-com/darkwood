import React, { useId, useImperativeHandle } from 'react';
import FlowHeader from './header.jsx';
import { flow } from './flow.jsx';

const createListFlow = (kind, title) => flow((props, ref) => {
  const id = useId();
  const { data, onUpdate } = props;
  const variable = data?.variable ?? '';
  const options = data?.options ?? (kind === 'checkboxes' ? {} : []);
  const selected = data?.selected ?? null;
  const update = changes => onUpdate({ variable, options, selected, ...changes });

  useImperativeHandle(ref, () => ({
    onDeserialize(raw) {
      const [variable, options, selected] = (typeof raw === 'string' ? JSON.parse(raw) : raw) ?? [];
      return { variable, options, selected };
    },
    onSerialize() {
      return JSON.stringify(kind === 'select' ? [variable, options, selected] : [variable, options]);
    },
    onExecute(runner) {
      if (!/^[a-zA-Z_$][\w$]*$/.test(variable) || ['__proto__', 'constructor', 'prototype', 'window', 'document', 'console', 'fetch'].includes(variable)) return;

      const context = runner.getContext();
      if (kind === 'text-list') {
        context[variable] = options;
      } else if (kind === 'checkboxes' && Array.isArray(context[variable])) {
        const values = context[variable];
        const flags = Object.fromEntries(values.map(value => [value, options[value] === true]));
        context[variable] = values.filter(value => flags[value]);
        update({ options: flags });
      } else if (kind === 'select' && Object.hasOwn(context, variable)) {
        const choices = context[variable];
        if (choices && typeof choices === 'object') update({ options: choices });
        context[variable] = selected;
      }
    },
  }), [data]);

  const entries = kind === 'select' && Array.isArray(options)
    ? options.map(value => [value, value])
    : Object.entries(options);
  const selectedValue = Array.isArray(options) && !options.includes(selected) && typeof selected === 'string'
    ? options[selected] ?? ''
    : selected ?? '';

  return (
    <>
      <FlowHeader {...props} title={title} />
      <form className="form-sm-horizontal" onSubmit={event => event.preventDefault()}>
        <label htmlFor={`${id}-variable`} className="form-label">Variable</label>
        <input
          id={`${id}-variable`}
          className="form-control mb-3"
          value={variable}
          onChange={event => update({ variable: event.target.value })}
        />
        {kind === 'checkboxes' ? (
          <fieldset className="mb-3">
            <legend className="fs-6">Choices</legend>
            {entries.map(([label, checked]) => (
              <label key={label} className="form-check">
                <input
                  type="checkbox"
                  className="form-check-input"
                  checked={checked === true}
                  onChange={event => update({ options: { ...options, [label]: event.target.checked } })}
                />
                {label}
              </label>
            ))}
            {entries.length === 0 && <p>Run the preceding list to populate these choices.</p>}
          </fieldset>
        ) : (
          <>
            <label htmlFor={`${id}-options`} className="form-label">Options (one per line)</label>
            <textarea
              id={`${id}-options`}
              className="form-control mb-3"
              value={Object.values(options).join('\n')}
              onChange={event => update({ options: event.target.value.split('\n') })}
            />
            {kind === 'select' && (
              <>
                <label htmlFor={`${id}-select`} className="form-label">Selected option</label>
                <select
                  id={`${id}-select`}
                  className="form-select mb-3"
                  value={selectedValue}
                  onChange={event => update({ selected: event.target.value || null })}
                >
                  <option value="">Choose…</option>
                  {[...new Map(entries)].filter(([value]) => value !== '').map(([value, label]) => (
                    <option key={value} value={value}>{label}</option>
                  ))}
                </select>
              </>
            )}
          </>
        )}
      </form>
    </>
  );
});

export const TextListFlow = createListFlow('text-list', 'Text List');
export const CheckboxesFlow = createListFlow('checkboxes', 'Checkboxes');
export const SelectFlow = createListFlow('select', 'Select');
