import React, { useImperativeHandle } from 'react';
import { flow } from './flow.jsx';

export default flow(({ type, data }, ref) => {
  useImperativeHandle(ref, () => ({
    onSerialize: () => data.serialized,
    onDeserialize: serialized => ({ serialized }),
    onExecute: () => { throw new Error(`Unsupported flow: ${type}`); },
  }), [data, type]);

  return <p role="status">This component is unavailable in the web editor: {type}</p>;
});
