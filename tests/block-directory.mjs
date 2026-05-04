import { readFileSync, existsSync } from 'node:fs';
import { join } from 'node:path';

const root = process.cwd();
const plugin = readFileSync(join(root, 'partner-organizations', 'src', 'Plugin.php'), 'utf8');
const block = readFileSync(join(root, 'partner-organizations', 'src', 'Block.php'), 'utf8');
const blockJsonPath = join(root, 'partner-organizations', 'blocks', 'partner-directory', 'block.json');
const editorScriptPath = join(root, 'partner-organizations', 'blocks', 'partner-directory', 'index.js');
const blockJson = JSON.parse(readFileSync(blockJsonPath, 'utf8'));
const editorScript = readFileSync(editorScriptPath, 'utf8');

function expect(condition, message) {
  if (!condition) throw new Error(message);
}

expect(plugin.includes('new Block('), 'Plugin must compose the Partner Directory block service.');
expect(block.includes('register_block_type('), 'Partner Directory block must be registered with WordPress.');
expect(block.includes("'render_callback' => [$this, 'render']"), 'Partner Directory block must use a dynamic render callback.');
expect(block.includes('$this->shortcode->render('), 'Partner Directory block rendering must reuse shortcode rendering behavior.');
expect(block.includes('sanitize_title('), 'Partner Directory block category slug must be sanitized.');
expect(!block.includes('wp_enqueue_script('), 'Partner Directory block must not enqueue frontend JavaScript.');

expect(blockJson.name === 'partner-organizations/partner-directory', 'Block name must use the plugin namespace.');
expect(blockJson.title === 'Partner Directory', 'Block title must use Partner Directory language.');
expect(blockJson.description.includes('Partner Organizations'), 'Block description must mention Partner Organizations.');
expect(blockJson.attributes.categorySlug.type === 'string', 'Block must define an optional categorySlug string attribute.');
expect(blockJson.editorScript === 'file:./index.js', 'Block editor script must be editor-only metadata.');
expect(!Object.hasOwn(blockJson, 'script'), 'Block must not define frontend JavaScript.');
expect(existsSync(editorScriptPath), 'Block editor script must exist.');
expect(editorScript.includes('InspectorControls'), 'Block must expose category filtering in the block sidebar.');
expect(editorScript.includes('useBlockProps'), 'Block edit output must use useBlockProps so Gutenberg can select and remove the block in the visual editor.');
expect(editorScript.includes('blockProps'), 'Block edit output must apply block props to a real wrapper element, not a fragment-only root.');
expect(editorScript.includes('categorySlug'), 'Block editor script must edit the categorySlug attribute.');
expect(editorScript.includes('Partner Category slug'), 'Block control label must use Partner Category language.');

console.log('Partner Directory block checks passed.');
