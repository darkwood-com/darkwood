# Design sources

The `960x640` files are the canonical editable atlases used by
`Godot/tools/extract_assets.py` to regenerate the runtime PNG assets.

The historical repository also contains exported working variants for nine
other resolutions. Those variants are reproducible from the canonical design
and are intentionally not part of this source tree.

The original audio masters are kept in `sound/`; the Godot project uses the
compressed runtime copies under `Godot/assets/sound/`.
